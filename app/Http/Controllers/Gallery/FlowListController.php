<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Albums\Flow;
use App\Assets\DbBool;
use App\Enum\MetricsAccess;
use App\Http\Requests\Flow\GetFlowListRequest;
use App\Http\Resources\Models\AlbumStatisticsResource;
use App\Http\Resources\V3\FlowListResource;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use function Safe\strtotime;

/**
 * Serves `GET /api/v3/Flow` (Feature 068, API-068-01): a lightweight,
 * unpaginated, Struct-of-Arrays listing of the caller's current Flow
 * scope/strategy, reusing {@see Flow::do()}'s existing query/policy/ordering
 * logic unchanged (FR-068-01). No nested photo data — each card's photo
 * preview is fetched separately via the `ratios` tier (FR-068-04).
 */
class FlowListController extends Controller
{
	public function __construct(
		protected Flow $flow,
		protected AlbumQueryPolicy $album_query_policy,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
	) {
	}

	public function index(GetFlowListRequest $request): FlowListResource
	{
		/** @var User|null $user */
		$user = Auth::user();

		// hide_nsfw_in_flow===true means Flow::do() has already excluded every
		// recursively-NSFW album via a whereNotExists() clause, and never adds
		// the virtual `is_recursive_nsfw` select in that case (see Flow::do()) -
		// so `is_nsfws[i]` is unconditionally false without needing to select
		// anything for it. Only when hide_nsfw_in_flow===false does Flow::do()
		// add that virtual column, and only then can/must we select it here.
		$hide_nsfw_in_flow = request()->configs()->getValueAsBool('hide_nsfw_in_flow');

		$query = $this->flow->do(false);
		$this->album_query_policy->joinBaseAlbumOwnerId($query, 'albums.id', 'v3_');
		$query->leftJoin('users', 'users.id', '=', 'v3_base_albums.owner_id');
		$query->leftJoin('statistics as v3_stats', function ($join): void {
			$join->on('v3_stats.album_id', '=', 'albums.id')->whereNull('v3_stats.photo_id');
		});

		// Note: addSelect() only - Flow::do() may already have added its own
		// virtual `is_recursive_nsfw` select; the replacing select() would
		// silently drop it.
		$query->addSelect([
			'albums.id',
			'albums.cover_id',
			'albums.num_photos',
			'albums.num_children',
			'albums.min_taken_at',
			'albums.max_taken_at',
			'v3_base_albums.title',
			'v3_base_albums.description',
			'v3_base_albums.owner_id',
			'pc_base_album.created_at',
			'pc_base_album.published_at',
			'v3_base_albums.published_at_orig_tz',
			'users.display_name',
			'users.username',
			'v3_stats.visit_count',
			'v3_stats.download_count',
			'v3_stats.shared_count',
		]);
		if (!$hide_nsfw_in_flow) {
			$query->addSelect(['is_recursive_nsfw']);
		}

		$rows = $query->toBase()->get();

		return $this->buildFlowListResource($rows, $user);
	}

	/**
	 * @param Collection<int,object> $rows
	 */
	private function buildFlowListResource(Collection $rows, ?User $user): FlowListResource
	{
		$is_authenticated = $user !== null;
		$hide_nsfw_in_flow = request()->configs()->getValueAsBool('hide_nsfw_in_flow');
		$flow_min_max_enabled = request()->configs()->getValueAsBool('flow_min_max_enabled');
		$min_max_date_format = request()->configs()->getValueAsString('date_format_flow_min_max');
		$flow_min_max_order = request()->configs()->getValueAsString('flow_min_max_order');
		$published_date_format = request()->configs()->getValueAsString('date_format_flow_published');
		$metrics_enabled = request()->configs()->getValueAsBool('metrics_enabled');
		$flow_display_statistics = request()->configs()->getValueAsBool('flow_display_statistics');
		$metrics_access = request()->configs()->getValueAsEnum('metrics_access', MetricsAccess::class);

		$ids = [];
		$titles = [];
		$descriptions = [];
		$cover_ids = [];
		$owner_names = [];
		$is_nsfws = [];
		$num_photos = [];
		$num_children = [];
		$min_max_texts = [];
		$published_created_ats = [];
		$diff_published_created_ats = [];
		$statistics = [];

		foreach ($rows as $row) {
			$ids[] = $row->id;
			$titles[] = $row->title;
			$descriptions[] = $this->resolveDescription($row->id, $row->description ?? '');
			$cover_ids[] = $row->cover_id;
			$owner_names[] = $is_authenticated ? ($row->display_name ?? $row->username) : null;
			$is_nsfws[] = $hide_nsfw_in_flow === false && DbBool::parse($row->is_recursive_nsfw ?? false);
			$num_photos[] = (int) $row->num_photos;
			$num_children[] = (int) $row->num_children;
			$min_max_texts[] = $this->resolveMinMaxText($row, $flow_min_max_enabled, $min_max_date_format, $flow_min_max_order);

			$published_at = $this->resolvePublishedAt($row);
			$published_created_ats[] = $published_at->format($published_date_format);
			$diff_published_created_ats[] = $published_at->diffForHumans();

			$can_read_metrics = match ($metrics_access) {
				MetricsAccess::PUBLIC => true,
				MetricsAccess::LOGGED_IN => $is_authenticated,
				MetricsAccess::OWNER => $is_authenticated && (int) $row->owner_id === $user?->id,
				MetricsAccess::ADMIN => $user?->may_administrate === true,
				default => false,
			};
			$statistics[] = ($metrics_enabled && $flow_display_statistics && $can_read_metrics)
				? new AlbumStatisticsResource(
					visit_count: (int) ($row->visit_count ?? 0),
					download_count: (int) ($row->download_count ?? 0),
					shared_count: (int) ($row->shared_count ?? 0),
				)
				: null;
		}

		return new FlowListResource(
			ids: $ids,
			titles: $titles,
			descriptions: $descriptions,
			cover_ids: $cover_ids,
			owner_names: $owner_names,
			is_nsfws: $is_nsfws,
			num_photos: $num_photos,
			num_children: $num_children,
			min_max_texts: $min_max_texts,
			published_created_ats: $published_created_ats,
			diff_published_created_ats: $diff_published_created_ats,
			statistics: $statistics,
		);
	}

	/**
	 * Markdown-converted `description`, cached per album (FR-068-07) — a pure
	 * function of `(album_id, description content)`, independent of viewer.
	 */
	private function resolveDescription(string $album_id, string $description): string
	{
		if ($description === '') {
			return '';
		}

		$enabled = request()->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = request()->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$this->cache_key_provider->flowDescriptionKey($album_id),
			[$this->cache_key_provider->flowDescriptionTag($album_id)],
			fn (): string => Markdown::convert(trim($description))->getContent(),
			ttl: $ttl,
		);
	}

	/**
	 * Resolves the publish/fallback instant in its correct original timezone,
	 * mirroring `DateTimeWithTimezoneCast::get()`'s exact behaviour for a
	 * fully-hydrated `$album->published_at` — required here since this
	 * controller reads raw, non-Eloquent-hydrated rows (`toBase()`), which
	 * bypass the cast entirely.
	 *
	 * `UTCBasedTimes::asDateTime()` defines a raw, timezone-less SQL datetime
	 * string as relative to UTC, *not* `published_at_orig_tz` — the original
	 * timezone is only ever applied as a second, explicit `setTimezone()`
	 * step afterwards, exactly like the cast itself does. Parsing the raw
	 * value directly as `published_at_orig_tz` would silently shift the
	 * instant itself, not just its display timezone.
	 *
	 * `created_at` (the fallback for an opted-out album) has no orig-tz
	 * companion of its own — `BaseAlbumImpl::$casts` casts it as a plain
	 * `'datetime'`, which resolves to the application's default timezone,
	 * not a per-row recorded one.
	 */
	private function resolvePublishedAt(object $row): Carbon
	{
		if ($row->published_at !== null) {
			return Carbon::parse($row->published_at, 'UTC')->setTimezone((string) $row->published_at_orig_tz);
		}

		return Carbon::parse($row->created_at, 'UTC')->setTimezone(date_default_timezone_get());
	}

	/**
	 * Mirrors `FlowItemResource::setMinMax()` exactly, using native
	 * `date()`/`strtotime()` instead of Carbon (`[[feedback_avoid_carbon_server_side]]`).
	 */
	private function resolveMinMaxText(object $row, bool $enabled, string $format, string $order): ?string
	{
		if (!$enabled) {
			return null;
		}

		$min_raw = $row->min_taken_at ?? null;
		$max_raw = $row->max_taken_at ?? null;
		if ($min_raw === null || $max_raw === null) {
			return null;
		}

		$min = date($format, strtotime((string) $min_raw));
		$max = date($format, strtotime((string) $max_raw));

		if ($min === $max) {
			return $min;
		}

		return $order === 'younger_older' ? "{$max} - {$min}" : "{$min} - {$max}";
	}
}
