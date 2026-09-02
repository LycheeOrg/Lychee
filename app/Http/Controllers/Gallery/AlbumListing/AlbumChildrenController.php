<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery\AlbumListing;

use App\Constants\AccessPermissionConstants as APC;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\AlbumSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TitleBucketMode;
use App\Http\Controllers\Gallery\AlbumListController;
use App\Http\Requests\Album\GetAlbumBucketsRequest;
use App\Http\Requests\Album\GetAlbumChildrenDataRequest;
use App\Http\Requests\Album\GetAlbumChildrenRightsRequest;
use App\Http\Resources\V3\AlbumBucketResource;
use App\Http\Resources\V3\AlbumChildrenDataResource;
use App\Http\Resources\V3\AlbumChildrenRightsResource;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function Safe\mktime;

/**
 * Serves the sub-album tier: `GET /api/v3/Albums/{album_id}`,
 * `/Albums/{album_id}/buckets`, `/Albums/{album_id}/rights` (Feature 061,
 * renamed by Feature 062, FR-062-12, Q-062-12 — the `/children` path segment
 * is dropped, mirroring root's own naming; the query logic and response
 * shape below are an unmodified, verbatim port of Feature 061's
 * `AlbumBucketController`/`AlbumChildrenDataController`/`AlbumChildrenRightsController`,
 * merged into one class per method — only the class/namespace boundary and
 * the route path changed, NFR-062-06).
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
		// can never produce more than one bucket (FR-061-06). Short-circuit
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
	 * Computes one display label per distinct bucket (FR-061-18) — bounded
	 * by bucket count, never by child-row count (NFR-061-01's scope
	 * explicitly exempts this: it runs entirely in PHP, after the
	 * `GROUP BY`).
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

	public function index(GetAlbumChildrenDataRequest $request): AlbumChildrenDataResource
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
			fn (): AlbumChildrenDataResource => $this->queryChildren($album, $user),
			ttl: $ttl,
		);
	}

	private function queryChildren(AbstractAlbum $album, ?User $user): AlbumChildrenDataResource
	{
		if ($album instanceof Album) {
			$query = Album::query()->where('parent_id', '=', $album->id);
			// applyVisibilityFilter() already joins computed_access_permissions
			// internally (prepareModelQueryOrFail()) — must not join it again.
			$query = $this->album_query_policy->applyVisibilityFilter($query, $user);

			$sorting = $album->getEffectiveAlbumSorting();
			$direction = $sorting->order === OrderSortingType::DESC ? 'desc' : 'asc';
			// Order by bucket_id first (mirrors queryBuckets() exactly,
			// "unknown" always last) so grouping these rows by bucket_id
			// reproduces the buckets endpoint's own row order; this is required, not
			// redundant with the effective-column order below, because under
			// title_bucket_mode=date_prefix the parsed-date bucket_id and the
			// title_base/title_index sort key are unrelated dimensions of the same
			// string, so same-bucket rows would not otherwise stay contiguous.
			$query->orderByRaw('(albums.bucket_id IS NULL) ASC')
				->orderBy('albums.bucket_id', $direction);
			(new SortingDecorator($query))->orderBy($sorting->column, $sorting->order)->applyOrdering();

			return $this->fetch($query, $user);
		}

		if ($album instanceof TagAlbum) {
			if (!$this->config_manager->getValueAsBool('TA_albums_listing_enabled')) {
				return $this->toResource(collect(), $user);
			}

			$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();
			$tag_ids = $album->tags->pluck('id')->all();
			$query = $this->album_repository->queryMatchingAlbumsForTag($tag_ids, $album->is_and, $unlocked_album_ids);
			// queryMatchingAlbumsForTag()/applyBrowsabilityFilter() do not join
			// computed_access_permissions on the outer query — add it here.
			$this->album_query_policy->joinSubComputedAccessPermissions($query, 'albums.id', 'left', '', false, $user);
			// No parent-governed bucket_id concept applies to a dynamically-matched,
			// disparately-parented result set (tier 1 excludes these types entirely) -
			// order by the same instance-wide default v2's paginated listing already
			// uses for this type.
			$default_sorting = AlbumSortingCriterion::createDefault();
			(new SortingDecorator($query))->orderBy($default_sorting->column, $default_sorting->order)->applyOrdering();

			return $this->fetch($query, $user);
		}

		// PersonAlbum — the only remaining type GetAlbumChildrenDataRequest resolves.
		if (!$this->config_manager->getValueAsBool('PA_albums_listing_enabled')) {
			return $this->toResource(collect(), $user);
		}

		/** @var PersonAlbum $album */
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();
		$query = $this->album_repository->queryMatchingAlbumsForPerson($album, $unlocked_album_ids);
		$this->album_query_policy->joinSubComputedAccessPermissions($query, 'albums.id', 'left', '', false, $user);
		$default_sorting = AlbumSortingCriterion::createDefault();
		(new SortingDecorator($query))->orderBy($default_sorting->column, $default_sorting->order)->applyOrdering();

		return $this->fetch($query, $user);
	}

	/**
	 * @param Builder<Album> $query
	 */
	private function fetch(Builder $query, ?User $user): AlbumChildrenDataResource
	{
		// Feature 061 (FR-061-27): the album's own public/anonymous grant,
		// independent of the requesting viewer — distinct from
		// computed_access_permissions above, which reflects the *viewer's*
		// effective access. A unique index on
		// (base_album_id, user_id_unique_key, user_group_id_unique_key)
		// guarantees at most one such row per album, so this left join can
		// never fan out the result set. Joined via a narrow-column subquery
		// (mirrors joinBaseAlbumOwnerId()/joinSubComputedAccessPermissions()'s
		// own convention), not a raw table join — access_permissions carries
		// its own created_at/updated_at columns that would otherwise collide
		// with SortingDecorator's unqualified `ORDER BY created_at`.
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

		return $this->toResource($rows, $user);
	}

	/**
	 * @param Collection<int,object{id:string,title:string,description:?string,cover_id:?string,auto_cover_id_max_privilege:?string,auto_cover_id_least_privilege:?string,owner_id:int,bucket_id:?string,password:?string,is_nsfw:mixed,is_pinned:mixed,public_grant_id:?string,public_is_link_required:mixed,num_children:int|string,num_photos:int|string,created_at:string,min_taken_at:?string,max_taken_at:?string}> $rows
	 */
	private function toResource(Collection $rows, ?User $user): AlbumChildrenDataResource
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
			$bucket_ids[] = $row->bucket_id ?? 'unknown';
			$owner_ids[] = (string) $row->owner_id;
			$is_password_requireds[] = $row->password !== null;
			$is_nsfws[] = filter_var($row->is_nsfw, FILTER_VALIDATE_BOOLEAN);
			$is_pinneds[] = filter_var($row->is_pinned, FILTER_VALIDATE_BOOLEAN);
			$is_publics[] = $row->public_grant_id !== null;
			$is_link_requireds[] = filter_var($row->public_is_link_required, FILTER_VALIDATE_BOOLEAN);
			$has_subalbums[] = ((int) $row->num_children) > 0;
			$num_photos[] = (int) $row->num_photos;
			$num_subalbums[] = (int) $row->num_children;
			$created_ats[] = $row->created_at;
			$min_taken_ats[] = $row->min_taken_at;
			$max_taken_ats[] = $row->max_taken_at;
		}

		return new AlbumChildrenDataResource(
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

	// ── GET /Albums/{album_id}/rights ───────────────────────────────

	public function rights(GetAlbumChildrenRightsRequest $request): AlbumChildrenRightsResource
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
			fn (): AlbumChildrenRightsResource => $this->queryRights($album, $user),
			ttl: $ttl,
		);
	}

	private function queryRights(AbstractAlbum $album, ?User $user): AlbumChildrenRightsResource
	{
		if ($album instanceof Album) {
			return $this->queryRightsForAlbum($album, $user);
		}

		if ($album instanceof TagAlbum) {
			if (!$this->config_manager->getValueAsBool('TA_albums_listing_enabled')) {
				return $this->emptyMatchingAlbumsResource($album);
			}

			$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();
			$tag_ids = $album->tags->pluck('id')->all();
			$query = $this->album_repository->queryMatchingAlbumsForTag($tag_ids, $album->is_and, $unlocked_album_ids);

			return $this->queryRightsForMatchingAlbums($album, $query, $user);
		}

		// PersonAlbum — the only remaining type GetAlbumChildrenRightsRequest resolves.
		if (!$this->config_manager->getValueAsBool('PA_albums_listing_enabled')) {
			return $this->emptyMatchingAlbumsResource($album);
		}

		/** @var PersonAlbum $album */
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();
		$query = $this->album_repository->queryMatchingAlbumsForPerson($album, $unlocked_album_ids);

		return $this->queryRightsForMatchingAlbums($album, $query, $user);
	}

	private function queryRightsForAlbum(Album $album, ?User $user): AlbumChildrenRightsResource
	{
		$is_admin = $user?->may_administrate === true;

		$query = Album::query()->where('parent_id', '=', $album->id);
		$query = $this->album_query_policy->applyVisibilityFilter($query, $user);

		if ($is_admin) {
			// Mirrors AlbumQueryPolicy::applyVisibilityFilter()/applyReachabilityFilter()'s
			// own admin early-return (NFR-061-10) — neither the grants join
			// nor the can_delete_children exists() query ever runs.
			$ids = $query->select(['albums.id'])->toBase()->pluck('id')->all();
			$count = count($ids);

			return new AlbumChildrenRightsResource(
				owner_id: (string) $album->owner_id,
				can_delete_children: true,
				can_move_children: true,
				ids: $ids,
				grants_edit: array_fill(0, $count, true),
				grants_download: array_fill(0, $count, true),
			);
		}

		[$ids, $grants_edit, $grants_download] = $this->fetchGrants($query, $user);
		$can_delete_children = $this->canDeleteChildren($album, $user);

		return new AlbumChildrenRightsResource(
			owner_id: (string) $album->owner_id,
			can_delete_children: $can_delete_children,
			can_move_children: $can_delete_children,
			ids: $ids,
			grants_edit: $grants_edit,
			grants_download: $grants_download,
		);
	}

	/**
	 * @param Builder<Album> $query
	 */
	private function queryRightsForMatchingAlbums(TagAlbum|PersonAlbum $album, Builder $query, ?User $user): AlbumChildrenRightsResource
	{
		$is_admin = $user?->may_administrate === true;

		if ($is_admin) {
			$ids = $query->select(['albums.id'])->toBase()->pluck('id')->all();
			$count = count($ids);

			return new AlbumChildrenRightsResource(
				owner_id: (string) $album->owner_id,
				can_delete_children: false,
				can_move_children: false,
				ids: $ids,
				grants_edit: array_fill(0, $count, true),
				grants_download: array_fill(0, $count, true),
			);
		}

		[$ids, $grants_edit, $grants_download] = $this->fetchGrants($query, $user);

		return new AlbumChildrenRightsResource(
			owner_id: (string) $album->owner_id,
			// No single shared parent's access_permissions could uniformly
			// apply to a dynamically-matched, disparately-parented set.
			can_delete_children: false,
			can_move_children: false,
			ids: $ids,
			grants_edit: $grants_edit,
			grants_download: $grants_download,
		);
	}

	private function emptyMatchingAlbumsResource(TagAlbum|PersonAlbum $album): AlbumChildrenRightsResource
	{
		return new AlbumChildrenRightsResource(
			owner_id: (string) $album->owner_id,
			can_delete_children: false,
			can_move_children: false,
			ids: [],
			grants_edit: [],
			grants_download: [],
		);
	}

	/**
	 * `getComputedAccessPermissionSubQuery(full: true, ...)` applies no
	 * internal `GROUP BY` (FR-061-21) — a caller in multiple groups with
	 * separate matching grants on the same child would otherwise produce
	 * duplicate joined rows. `GROUP BY` + `MAX()` here correctly OR-merges
	 * them: any matching group/user/public row granting a right is enough
	 * (NFR-061-09).
	 *
	 * @param Builder<Album> $query
	 *
	 * @return array{0:string[],1:bool[],2:bool[]}
	 */
	private function fetchGrants(Builder $query, ?User $user): array
	{
		$this->album_query_policy->joinSubComputedAccessPermissions($query, 'albums.id', 'left', 'grants_', true, $user);

		$rows = $query
			->select(['albums.id'])
			->selectRaw('MAX(grants_computed_access_permissions.grants_edit) as grants_edit')
			->selectRaw('MAX(grants_computed_access_permissions.grants_download) as grants_download')
			->groupBy('albums.id')
			->toBase()
			->get();

		$ids = [];
		$grants_edit = [];
		$grants_download = [];
		foreach ($rows as $row) {
			$ids[] = $row->id;
			$grants_edit[] = filter_var($row->grants_edit, FILTER_VALIDATE_BOOLEAN);
			$grants_download[] = filter_var($row->grants_download, FILTER_VALIDATE_BOOLEAN);
		}

		return [$ids, $grants_edit, $grants_download];
	}

	/**
	 * Mirrors {@see \App\Policies\AlbumPolicy::canDelete()}'s parent-scoped
	 * `AccessPermission` query verbatim, addressed directly at `$album->id`
	 * (equivalent to `$abstract_album->parent_id` there, since every
	 * returned child's parent *is* `$album->id`).
	 */
	private function canDeleteChildren(Album $album, ?User $user): bool
	{
		if ($user === null) {
			return false;
		}

		return AccessPermission::query()
			->where(APC::BASE_ALBUM_ID, '=', $album->id)
			->where(
				fn ($query) => $query->where(APC::USER_ID, '=', $user->id)
					->orWhereIn(APC::USER_GROUP_ID, $user->user_groups->pluck('id'))
			)
			->where(APC::GRANTS_DELETE, '=', true)
			->exists();
	}
}
