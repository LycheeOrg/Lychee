<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Contracts\Models\AbstractAlbum;
use App\DTO\AlbumSortingCriterion;
use App\Enum\OrderSortingType;
use App\Http\Requests\Album\GetAlbumChildrenDataRequest;
use App\Http\Resources\V3\AlbumChildrenDataResource;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\PersonAlbum;
use App\Models\TagAlbum;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use App\Repositories\AlbumRepository;
use App\Repositories\ConfigManager;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Serves `GET /api/v3/Albums/{album_id}/children` (Feature 061,
 * API-061-02): the per-direct-child render data an album tile needs, as one
 * whole-album-at-once Struct-of-Arrays response, built from a single flat
 * `toBase()` query with zero joins beyond {@see AlbumQueryPolicy::applyVisibilityFilter()}'s
 * own (NFR-061-07) — identical scoping/policy to
 * {@see \App\Repositories\AlbumRepository::getChildrenPaginated()} and
 * {@see AlbumBucketController}'s own query (NFR-061-08). For a regular
 * {@see Album}, rows are ordered by bucket_id first (matching
 * {@see AlbumBucketController}'s own order exactly) then by the parent's
 * effective sort criterion, so grouping this endpoint's rows by bucket_id and
 * slicing by the buckets endpoint's per-bucket counts reproduces that
 * endpoint's own row order (FR-061-26). {@see TagAlbum}/{@see PersonAlbum}
 * rows are ordered by the instance-wide default sort criterion instead — no
 * bucket_id concept applies to a dynamically-matched, disparately-parented
 * result set.
 *
 * For a {@see TagAlbum}/{@see PersonAlbum}, "children" means the same
 * "matching albums" listing {@see AlbumRepository::queryMatchingAlbumsForTag()}/
 * {@see AlbumRepository::queryMatchingAlbumsForPerson()} already build for
 * v2's `AlbumChildrenController` — reused here unpaginated,
 * `toBase()`-queried like the rest of this feature.
 */
class AlbumChildrenDataController extends Controller
{
	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
		protected AlbumRepository $album_repository,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
		protected ConfigManager $config_manager,
	) {
	}

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
			// Order by bucket_id first (mirrors AlbumBucketController::queryBuckets()
			// exactly, "unknown" always last) so grouping these rows by bucket_id
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
			// uses for this type (AlbumChildrenController::get()).
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
}
