<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery\AlbumListing;

use App\Actions\Album\StructOfArrays\QueryChildrenForAlbum;
use App\Actions\Album\StructOfArrays\QueryChildrenForPerson;
use App\Actions\Album\StructOfArrays\QueryChildrenForTag;
use App\Actions\Album\StructOfArrays\QueryRightsForAlbum;
use App\Actions\Album\StructOfArrays\QueryRightsForMatchingAlbums;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TitleBucketMode;
use App\Http\Requests\Album\GetAlbumBucketsRequest;
use App\Http\Requests\Album\GetAlbumChildrenDataRequest;
use App\Http\Requests\Album\GetAlbumChildrenRightsRequest;
use App\Http\Resources\V3\AlbumBucketResource;
use App\Http\Resources\V3\AlbumDataResource;
use App\Http\Resources\V3\AlbumRightsResource;
use App\Models\Album;
use App\Models\PersonAlbum;
use App\Models\TagAlbum;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use App\Repositories\AlbumRepository;
use App\Repositories\ConfigManager;
use App\Services\AlbumBucketComputer;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use function Safe\mktime;

/**
 * Serves the sub-album tier: `GET /api/v3/Albums/{album_id}`,
 * `/Albums/{album_id}/buckets`, `/Albums/{album_id}/rights` — the `/children`
 * path segment is dropped, mirroring root's own naming; the query logic and
 * response shape below are an unmodified, verbatim port of the former
 * `AlbumBucketController`/`AlbumChildrenDataController`/`AlbumChildrenRightsController`,
 * merged into one class per method — only the class/namespace boundary and
 * the route path changed.
 */
class AlbumChildrenController extends Controller
{
	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
		protected AlbumBucketComputer $bucket_computer,
		protected AlbumRepository $album_repository,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
		protected ConfigManager $config_manager,
		protected QueryChildrenForAlbum $query_children_for_album,
		protected QueryChildrenForTag $query_children_for_tag,
		protected QueryChildrenForPerson $query_children_for_person,
		protected QueryRightsForAlbum $query_rights_for_album,
		protected QueryRightsForMatchingAlbums $query_rights_for_matching_albums,
	) {
	}

	// ── GET /Albums/{album_id}/buckets ─────────────────────────────

	public function buckets(GetAlbumBucketsRequest $request): AlbumBucketResource
	{
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();

		$key = $this->cache_key_provider->albumBucketsKey($album->id, $user?->id);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->albumChildrenTag($album->id),
				$this->cache_key_provider->userTag($user?->id),
			],
			fn (): AlbumBucketResource => $this->queryBuckets($album, $user),
			ttl: $ttl,
		);
	}

	private function queryBuckets(Album $album, ?User $user): AlbumBucketResource
	{
		$sorting = $album->getEffectiveAlbumSorting();

		// OWNER_ID is a valid effective sort column, but every direct child
		// of a given album always shares that album's exact owner_id -> it
		// can never produce more than one bucket. Short-circuit
		// without ever running a GROUP BY.
		if ($sorting->column === ColumnSortingType::OWNER_ID) {
			return new AlbumBucketResource(bucket_ids: [], counts: [], labels: [], bucketable: false);
		}

		$query = Album::query()->where('parent_id', '=', $album->id);
		$query = $this->album_query_policy->applyVisibilityFilter($query, $user);

		$direction = $sorting->order === OrderSortingType::DESC ? 'desc' : 'asc';

		$rows = $query
			->select(['albums.bucket_id'])
			->selectRaw('COUNT(*) as bucket_count')
			->groupBy('albums.bucket_id')
			// NULL ("unknown") always sorts last, regardless of $direction.
			->orderByRaw('(albums.bucket_id IS NULL) ASC')
			->orderBy('albums.bucket_id', $direction)
			->toBase()
			->get();

		$bucket_ids = [];
		$counts = [];
		foreach ($rows as $row) {
			$bucket_ids[] = $row->bucket_id ?? 'unknown';
			$counts[] = (int) $row->bucket_count;
		}

		$labels = $this->computeLabels($bucket_ids, $sorting->column, $album->album_timeline);

		return new AlbumBucketResource(bucket_ids: $bucket_ids, counts: $counts, labels: $labels, bucketable: true);
	}

	/**
	 * Computes one display label per distinct bucket — bounded
	 * by bucket count, never by child-row count: it runs entirely in PHP,
	 * after the `GROUP BY`.
	 *
	 * @param string[] $bucket_ids
	 */
	private function computeLabels(array $bucket_ids, ColumnSortingType $sorting_column, ?TimelineAlbumGranularity $album_timeline): array
	{
		$is_alphabetical_title = $sorting_column === ColumnSortingType::TITLE &&
			(request()->configs()->getValueAsEnum('title_bucket_mode', TitleBucketMode::class) ?? TitleBucketMode::DATE_PREFIX) === TitleBucketMode::ALPHABETICAL;

		if ($is_alphabetical_title) {
			// Already human-readable; never date-parsed.
			return $bucket_ids;
		}

		$granularity = $this->bucket_computer->resolveGranularity($album_timeline);
		$format = match ($granularity) {
			TimelineAlbumGranularity::YEAR => request()->configs()->getValueAsString('timeline_album_date_format_year'),
			TimelineAlbumGranularity::MONTH => request()->configs()->getValueAsString('timeline_album_date_format_month'),
			TimelineAlbumGranularity::DAY => request()->configs()->getValueAsString('timeline_album_date_format_day'),
			default => request()->configs()->getValueAsString('timeline_album_date_format_year'),
		};

		return array_map(
			fn (string $bucket_id): string => $bucket_id === 'unknown' ? 'unknown' : $this->formatBucketLabel($bucket_id, $format),
			$bucket_ids,
		);
	}

	/**
	 * Formats one `bucket_id` (`"Y"`/`"Y-m"`/`"Y-m-d"`, the exact truncation
	 * format {@see AlbumBucketComputer::truncateDate()} writes) against an
	 * arbitrary admin-configured PHP `date()` format string — via `mktime()` +
	 * `date()`, not a `Carbon`/`DateTime` object.
	 */
	private function formatBucketLabel(string $bucket_id, string $format): string
	{
		$parts = explode('-', $bucket_id);
		$year = (int) $parts[0];
		$month = (int) ($parts[1] ?? 1);
		$day = (int) ($parts[2] ?? 1);

		return date($format, mktime(0, 0, 0, $month, $day, $year));
	}

	// ── GET /Albums/{album_id} ──────────────────────────────────────

	public function index(GetAlbumChildrenDataRequest $request): AlbumDataResource
	{
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();

		// TagAlbum/PersonAlbum "matching albums" results are curated by
		// AlbumPolicy::getUnlockedAlbumIDs() (session-scoped), which can
		// change between requests from the very same user/identity - the
		// cache key must vary with it too, mirroring the pre-existing
		// AlbumRepository::getMatchingAlbumsForTagPaginated()/...ForPersonPaginated()
		// convention exactly. A regular Album's own direct-children listing
		// does not depend on this state at all (visibility is governed by
		// applyVisibilityFilter() alone), so it always gets the same, empty
		// digest.
		$unlocked_digest = ($album instanceof TagAlbum || $album instanceof PersonAlbum)
			? $this->cache_key_provider->unlockedAlbumsDigest()
			: '';

		$key = $this->cache_key_provider->albumChildrenDataKey($album->get_id(), $user?->id, $unlocked_digest);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->albumChildrenTag($album->get_id()),
				$this->cache_key_provider->userTag($user?->id),
			],
			fn (): AlbumDataResource => $this->queryChildren($album, $user),
			ttl: $ttl,
		);
	}

	private function queryChildren(AbstractAlbum $album, ?User $user): AlbumDataResource
	{
		if ($album instanceof Album) {
			return $this->query_children_for_album->do($album, $user);
		}

		if ($album instanceof TagAlbum) {
			return $this->query_children_for_tag->do($album, $user);
		}

		/** @var PersonAlbum $album */
		return $this->query_children_for_person->do($album, $user);
	}

	// ── GET /Albums/{album_id}/rights ───────────────────────────────

	public function rights(GetAlbumChildrenRightsRequest $request): AlbumRightsResource
	{
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();

		// See index() for why the unlocked-album digest must be part of this
		// key for TagAlbum/PersonAlbum.
		$unlocked_digest = ($album instanceof TagAlbum || $album instanceof PersonAlbum)
			? $this->cache_key_provider->unlockedAlbumsDigest()
			: '';

		$key = $this->cache_key_provider->albumChildrenRightsKey($album->get_id(), $user?->id, $unlocked_digest);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->albumChildrenTag($album->get_id()),
				$this->cache_key_provider->userTag($user?->id),
			],
			fn (): AlbumRightsResource => $this->queryRights($album, $user),
			ttl: $ttl,
		);
	}

	private function queryRights(AbstractAlbum $album, ?User $user): AlbumRightsResource
	{
		if ($album instanceof Album) {
			return $this->query_rights_for_album->do($album, $user);
		}

		if ($album instanceof TagAlbum) {
			if (!$this->config_manager->getValueAsBool('TA_albums_listing_enabled')) {
				return $this->query_rights_for_matching_albums->emptyResource($album);
			}

			$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();
			$tag_ids = $album->tags->pluck('id')->all();
			$query = $this->album_repository->queryMatchingAlbumsForTag($tag_ids, $album->is_and, $unlocked_album_ids);

			return $this->query_rights_for_matching_albums->do($album, $query, $user);
		}

		// PersonAlbum — the only remaining type GetAlbumChildrenRightsRequest resolves.
		if (!$this->config_manager->getValueAsBool('PA_albums_listing_enabled')) {
			return $this->query_rights_for_matching_albums->emptyResource($album);
		}

		/** @var PersonAlbum $album */
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();
		$query = $this->album_repository->queryMatchingAlbumsForPerson($album, $unlocked_album_ids);

		return $this->query_rights_for_matching_albums->do($album, $query, $user);
	}
}
