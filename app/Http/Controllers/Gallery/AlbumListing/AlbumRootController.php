<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery\AlbumListing;

use App\Assets\DbBool;
use App\DTO\AlbumSortingCriterion;
use App\Enum\AlbumListingScope;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TitleBucketMode;
use App\Http\Controllers\Gallery\AlbumListController;
use App\Http\Requests\Album\GetScopedAlbumsRequest;
use App\Http\Resources\V3\AlbumBucketResource;
use App\Http\Resources\V3\AlbumDataResource;
use App\Http\Resources\V3\AlbumRightsResource;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use App\Repositories\ConfigManager;
use App\Services\AlbumBucketComputer;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function Safe\mktime;
use Spatie\LaravelData\Optional;

/**
 * Serves the root tier: `GET /api/v3/Albums/root[/buckets|/rights]`
 * root albums (`parent_id IS NULL`) get the same buckets/index/rights
 * trio as sub-albums, plus a `scope` (`own`\|`shared`) dimension
 * reproducing today's `Top::get()` owned/shared partition.
 * `own` scope reuses the exact bucket_id/instance-default-sorting mechanism sub-albums use;
 * `shared` scope groups by owner as a **live** `GROUP BY owner_id` at read time, never via the persisted `bucket_id` column.
 */
class AlbumRootController extends Controller
{
	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
		protected AlbumBucketComputer $bucket_computer,
		protected ConfigManager $config_manager,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
	) {
	}

	/**
	 * Base query shared by all three endpoints: every root album visible to
	 * `$user`, scoped by ownership, with the same `deduplicate_pinned_albums`
	 * conditional join {@see \App\Actions\Albums\Top::queryRootAlbums()}
	 * already applies.
	 *
	 * @return Builder<Album>
	 */
	private function baseQuery(AlbumListingScope $scope, ?User $user): Builder
	{
		$query = Album::query()->whereIsRoot();
		$query = $this->album_query_policy->applyVisibilityFilter($query, $user);

		if ($scope === AlbumListingScope::OWN) {
			// GetScopedAlbumsRequest guarantees $user !== null whenever OWN
			// is reachable (a guest can never pass scope=own, 422 otherwise).
			$query->where('base_albums.owner_id', '=', $user->id);
		} elseif ($user !== null) {
			$query->where('base_albums.owner_id', '!=', $user->id);
		}

		$query->when(
			$this->config_manager->getValueAsBool('deduplicate_pinned_albums'),
			fn ($q) => $q->joinSub(DB::table('base_albums')->select(['id', 'is_pinned'])->where('is_pinned', '=', false), 'not_pinned', 'not_pinned.id', '=', 'albums.id')
		);

		return $query;
	}

	// ── GET /Albums/root ────────────────────────────────────────────

	public function index(GetScopedAlbumsRequest $request): AlbumDataResource
	{
		$scope = $request->scope();
		/** @var User|null $user */
		$user = Auth::user();

		$key = $this->cache_key_provider->rootAlbumChildrenDataKey($scope, $user?->id);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->albumChildrenTag(null),
				$this->cache_key_provider->userTag($user?->id),
				$this->cache_key_provider->albumListingGlobalTag(),
			],
			fn (): AlbumDataResource => $this->queryChildren($scope, $user),
			ttl: $ttl,
		);
	}

	private function queryChildren(AlbumListingScope $scope, ?User $user): AlbumDataResource
	{
		$query = $this->baseQuery($scope, $user);
		$sorting = AlbumSortingCriterion::createDefault();
		$direction = $sorting->order === OrderSortingType::DESC ? 'desc' : 'asc';

		if ($scope === AlbumListingScope::OWN) {
			// Mirrors the sub-album tier exactly: order by the
			// already-persisted bucket_id (NULL last), then the effective
			// sort criterion.
			$query->orderByRaw('(albums.bucket_id IS NULL) ASC')
				->orderBy('albums.bucket_id', $direction);
		} else {
			// Owner-ordered first, never by the persisted
			// bucket_id column.
			$query->orderBy('base_albums.owner_id', 'asc');
		}
		(new SortingDecorator($query))->orderBy($sorting->column, $sorting->order)->applyOrdering();

		$query->joinSub(
			query: DB::table('access_permissions')
				->select(['base_album_id', 'is_link_required'])
				->whereNull('user_id')
				->whereNull('user_group_id'),
			as: 'public_access_permissions',
			first: 'public_access_permissions.base_album_id',
			operator: '=',
			second: 'albums.id',
			type: 'left'
		);

		$rows = $query
			->select([
				'albums.id',
				'base_albums.title',
				'albums.cover_id',
				'albums.auto_cover_id_max_privilege',
				'albums.auto_cover_id_least_privilege',
				'base_albums.owner_id',
				'albums.bucket_id',
				'computed_access_permissions.password',
				'base_albums.is_nsfw',
				'base_albums.is_pinned',
				'public_access_permissions.base_album_id as public_grant_id',
				'public_access_permissions.is_link_required as public_is_link_required',
				'albums.num_children',
				'albums.num_photos',
				'base_albums.created_at',
				'albums.min_taken_at',
				'albums.max_taken_at',
			])
			->selectRaw('SUBSTR(base_albums.description, 1, 100) as description')
			->toBase()
			->get();

		return $this->toAlbumDataResource($rows, $user, $scope);
	}

	/**
	 * @param Collection<int,object{id:string,title:string,description:?string,cover_id:?string,auto_cover_id_max_privilege:?string,auto_cover_id_least_privilege:?string,owner_id:int,bucket_id:?string,password:?string,is_nsfw:mixed,is_pinned:mixed,public_grant_id:?string,public_is_link_required:mixed,num_children:int|string,num_photos:int|string,created_at:string,min_taken_at:?string,max_taken_at:?string}> $rows
	 */
	private function toAlbumDataResource(Collection $rows, ?User $user, AlbumListingScope $scope): AlbumDataResource
	{
		$ids = [];
		$titles = [];
		$descriptions = [];
		$cover_ids = [];
		$bucket_ids = [];
		$owner_ids = [];
		$is_password_requireds = [];
		$is_nsfws = [];
		$is_pinneds = [];
		$is_publics = [];
		$is_link_requireds = [];
		$has_subalbums = [];
		$num_photos = [];
		$num_subalbums = [];
		$created_ats = [];
		$min_taken_ats = [];
		$max_taken_ats = [];

		foreach ($rows as $row) {
			$ids[] = $row->id;
			$titles[] = $row->title;
			$descriptions[] = $row->description ?? '';
			$cover_ids[] = AlbumListController::resolveCoverId($row, $user);
			// For shared scope, the bucket_id field carries the
			// row's own owner_id (never the persisted date/title column) so
			// grouping response rows by bucket_id reproduces the buckets
			// endpoint's own owner grouping.
			$bucket_ids[] = $scope === AlbumListingScope::SHARED ? (string) $row->owner_id : ($row->bucket_id ?? 'unknown');
			$owner_ids[] = (string) $row->owner_id;
			$is_password_requireds[] = $row->password !== null;
			$is_nsfws[] = DbBool::parse($row->is_nsfw);
			$is_pinneds[] = DbBool::parse($row->is_pinned);
			$is_publics[] = $row->public_grant_id !== null;
			$is_link_requireds[] = DbBool::parse($row->public_is_link_required);
			$has_subalbums[] = ((int) $row->num_children) > 0;
			$num_photos[] = (int) $row->num_photos;
			$num_subalbums[] = (int) $row->num_children;
			$created_ats[] = $row->created_at;
			$min_taken_ats[] = $row->min_taken_at;
			$max_taken_ats[] = $row->max_taken_at;
		}

		return new AlbumDataResource(
			ids: $ids,
			titles: $titles,
			descriptions: $descriptions,
			cover_ids: $cover_ids,
			bucket_ids: $bucket_ids,
			owner_ids: $owner_ids,
			is_password_requireds: $is_password_requireds,
			is_nsfws: $is_nsfws,
			is_pinneds: $is_pinneds,
			is_publics: $is_publics,
			is_link_requireds: $is_link_requireds,
			has_subalbums: $has_subalbums,
			num_photos: $num_photos,
			num_subalbums: $num_subalbums,
			created_ats: $created_ats,
			min_taken_ats: $min_taken_ats,
			max_taken_ats: $max_taken_ats,
		);
	}

	// ── GET /Albums/root/buckets ────────────────────────────────────

	public function buckets(GetScopedAlbumsRequest $request): AlbumBucketResource
	{
		$scope = $request->scope();
		/** @var User|null $user */
		$user = Auth::user();

		$key = $this->cache_key_provider->rootAlbumBucketsKey($scope, $user?->id);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->albumChildrenTag(null),
				$this->cache_key_provider->userTag($user?->id),
				$this->cache_key_provider->albumListingGlobalTag(),
			],
			fn (): AlbumBucketResource => $scope === AlbumListingScope::OWN
				? $this->queryOwnBuckets($user)
				: $this->querySharedBuckets($user),
			ttl: $ttl,
		);
	}

	private function queryOwnBuckets(?User $user): AlbumBucketResource
	{
		$sorting = AlbumSortingCriterion::createDefault();
		$query = $this->baseQuery(AlbumListingScope::OWN, $user);
		$direction = $sorting->order === OrderSortingType::DESC ? 'desc' : 'asc';

		$rows = $query
			->select(['albums.bucket_id'])
			->selectRaw('COUNT(*) as bucket_count')
			->groupBy('albums.bucket_id')
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

		$labels = $this->computeDateTitleLabels($bucket_ids, $sorting->column);

		return new AlbumBucketResource(bucket_ids: $bucket_ids, counts: $counts, labels: $labels, bucketable: true);
	}

	/**
	 * Mirrors {@see \App\Http\Controllers\Gallery\AlbumListing\AlbumChildrenController}'s
	 * `computeLabels()`, minus the `$album_timeline` per-parent override
	 * (root has no single parent to carry one — the granularity is purely
	 * instance-wide, same reasoning as {@see \App\Jobs\RecomputeRootAlbumBucketsJob}).
	 *
	 * @param string[] $bucket_ids
	 */
	private function computeDateTitleLabels(array $bucket_ids, ColumnSortingType $sorting_column): array
	{
		$is_alphabetical_title = $sorting_column === ColumnSortingType::TITLE &&
			(request()->configs()->getValueAsEnum('title_bucket_mode', TitleBucketMode::class) ?? TitleBucketMode::DATE_PREFIX) === TitleBucketMode::ALPHABETICAL;

		if ($is_alphabetical_title) {
			return $bucket_ids;
		}

		$granularity = $this->bucket_computer->resolveGranularity(null);
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

	private function formatBucketLabel(string $bucket_id, string $format): string
	{
		$parts = explode('-', $bucket_id);
		$year = (int) $parts[0];
		$month = (int) ($parts[1] ?? 1);
		$day = (int) ($parts[2] ?? 1);

		return date($format, mktime(0, 0, 0, $month, $day, $year));
	}

	/**
	 * A live `GROUP BY base_albums.owner_id` — never the
	 * persisted `bucket_id` column, never {@see AlbumBucketComputer}.
	 * `bucketable` is unconditionally `true`, even for a
	 * zero-result query. Label resolution is authentication-gated:
	 * the `users` join runs only for an authenticated caller;
	 * a guest never executes it at all, every label hardcoded `"unknown"`.
	 */
	private function querySharedBuckets(?User $user): AlbumBucketResource
	{
		$query = $this->baseQuery(AlbumListingScope::SHARED, $user);

		if ($user !== null) {
			$rows = (clone $query)
				->join('users', 'users.id', '=', 'base_albums.owner_id')
				->select(['base_albums.owner_id'])
				->selectRaw('COUNT(*) as bucket_count')
				->selectRaw('COALESCE(users.display_name, users.username) as label')
				->groupBy('base_albums.owner_id', 'users.display_name', 'users.username')
				->orderBy('base_albums.owner_id', 'asc')
				->toBase()
				->get();

			$bucket_ids = [];
			$counts = [];
			$labels = [];
			foreach ($rows as $row) {
				$bucket_ids[] = (string) $row->owner_id;
				$counts[] = (int) $row->bucket_count;
				$labels[] = $row->label;
			}

			return new AlbumBucketResource(bucket_ids: $bucket_ids, counts: $counts, labels: $labels, bucketable: true);
		}

		// Guest: the users join is never executed at all — not
		// merely hidden after the fact.
		$rows = $query
			->select(['base_albums.owner_id'])
			->selectRaw('COUNT(*) as bucket_count')
			->groupBy('base_albums.owner_id')
			->orderBy('base_albums.owner_id', 'asc')
			->toBase()
			->get();

		$bucket_ids = [];
		$counts = [];
		$labels = [];
		foreach ($rows as $row) {
			$bucket_ids[] = (string) $row->owner_id;
			$counts[] = (int) $row->bucket_count;
			$labels[] = 'unknown';
		}

		return new AlbumBucketResource(bucket_ids: $bucket_ids, counts: $counts, labels: $labels, bucketable: true);
	}

	// ── GET /Albums/root/rights ─────────────────────────────────────

	public function rights(GetScopedAlbumsRequest $request): AlbumRightsResource
	{
		$scope = $request->scope();
		/** @var User|null $user */
		$user = Auth::user();

		$key = $this->cache_key_provider->rootAlbumChildrenRightsKey($scope, $user?->id);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->albumChildrenTag(null),
				$this->cache_key_provider->userTag($user?->id),
				$this->cache_key_provider->albumListingGlobalTag(),
			],
			fn (): AlbumRightsResource => $this->queryRights($scope, $user),
			ttl: $ttl,
		);
	}

	/**
	 * Root has no single shared parent's `access_permissions` to
	 * check `can_delete_children`/`can_move_children` against — both flags
	 * are always `false` for a non-admin caller (either scope), `true` for
	 * an admin. `owner_id` is unconditionally omitted from the JSON payload
	 * — root has no single owner to report there, even under
	 * `own` scope.
	 */
	private function queryRights(AlbumListingScope $scope, ?User $user): AlbumRightsResource
	{
		$is_admin = $user?->may_administrate === true;
		$query = $this->baseQuery($scope, $user);

		if ($is_admin) {
			$ids = $query->select(['albums.id'])->toBase()->pluck('id')->all();
			$count = count($ids);

			return new AlbumRightsResource(
				owner_id: Optional::create(),
				can_delete_children: true,
				can_move_children: true,
				ids: $ids,
				grants_edit: array_fill(0, $count, true),
				grants_download: array_fill(0, $count, true),
			);
		}

		$this->album_query_policy->joinSubComputedAccessPermissions($query, 'albums.id', 'left', 'grants_', true, $user);

		// PostgreSQL has no `MAX()` aggregate for `boolean` (unlike MySQL/SQLite,
		// where a boolean is just an int); `bool_or()` is its equivalent.
		$or_aggregate = match (DB::getDriverName()) {
			'pgsql' => 'bool_or',
			default => 'MAX',
		};

		$rows = $query
			->select(['albums.id'])
			->selectRaw($or_aggregate . '(grants_computed_access_permissions.grants_edit) as grants_edit')
			->selectRaw($or_aggregate . '(grants_computed_access_permissions.grants_download) as grants_download')
			->groupBy('albums.id')
			->toBase()
			->get();

		$ids = [];
		$grants_edit = [];
		$grants_download = [];
		foreach ($rows as $row) {
			$ids[] = $row->id;
			$grants_edit[] = DbBool::parse($row->grants_edit);
			$grants_download[] = DbBool::parse($row->grants_download);
		}

		return new AlbumRightsResource(
			owner_id: Optional::create(),
			can_delete_children: false,
			can_move_children: false,
			ids: $ids,
			grants_edit: $grants_edit,
			grants_download: $grants_download,
		);
	}
}
