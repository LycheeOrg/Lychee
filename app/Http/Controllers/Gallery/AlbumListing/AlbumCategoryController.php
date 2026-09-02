<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery\AlbumListing;

use App\DTO\AlbumSortingCriterion;
use App\Enum\AlbumListingScope;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Factories\AlbumFactory;
use App\Http\Controllers\Gallery\AlbumListController;
use App\Http\Requests\Album\GetAlbumCategoryRequest;
use App\Http\Requests\Album\GetScopedAlbumsRequest;
use App\Http\Resources\V3\AlbumCategoryListResource;
use App\Http\Resources\V3\AlbumCategoryRightsResource;
use App\Models\Album;
use App\Models\AlbumUserThumb;
use App\Models\Extensions\SortingDecorator;
use App\Models\PersonAlbum;
use App\Models\TagAlbum;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use App\Repositories\ConfigManager;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Serves the flat, un-bucketed category listings (Feature 062, FR-062-09/10/15):
 * `GET /Albums/smart`, `/persons`, `/tags`, `/tags/rights`, `/pinned`.
 * Smart/tag albums stay un-scoped; persons/pinned additionally take the same
 * `own`\|`shared` split as root (FR-062-15), always as one flat, ungrouped
 * list — never per-owner buckets, never a `/buckets` route (NG9).
 */
class AlbumCategoryController extends Controller
{
	public function __construct(
		protected AlbumFactory $album_factory,
		protected AlbumQueryPolicy $album_query_policy,
		protected ConfigManager $config_manager,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
	) {
	}

	// ── GET /Albums/smart ────────────────────────────────────────────

	/**
	 * Reuses the existing cheap, in-memory, `Gate`-filtered
	 * `AlbumFactory::getAllBuiltInSmartAlbums(false)` list — no live `photos`
	 * query (Feature 062, FR-062-16 amendment narrowed this from "zero SQL
	 * queries" to "zero *photo* queries"): cover pixels come from one
	 * batched, indexed lookup against the pre-computed `album_user_thumbs`
	 * cache ({@link \App\Models\Extensions\CachesAlbumUserThumb}), the same
	 * cache `BaseSmartAlbum::getThumbAttribute()`/`RecomputeAlbumUserThumbsJob`
	 * already read/write for the v2 `Top::get()` path — never resolved live.
	 */
	public function smart(GetAlbumCategoryRequest $request): AlbumCategoryListResource
	{
		/** @var Collection<int,BaseSmartAlbum> $smart_albums */
		$smart_albums = $this->album_factory
			->getAllBuiltInSmartAlbums(false)
			->filter(fn (BaseSmartAlbum $smart_album) => Gate::check(AlbumPolicy::CAN_SEE, $smart_album))
			->values();

		$ids = $smart_albums->map(fn (BaseSmartAlbum $smart_album) => $smart_album->get_id())->all();

		/** @var array<string,string> $cached_covers album_id => photo_id */
		$cached_covers = AlbumUserThumb::query()
			->whereIn('album_id', $ids)
			->where('user_id', '=', Auth::id())
			->pluck('photo_id', 'album_id')
			->all();

		$titles = [];
		$cover_ids = [];
		$owner_ids = [];
		foreach ($smart_albums as $smart_album) {
			$titles[] = $smart_album->get_title();
			// Cache-only: a miss stays null (no live resolution) and
			// self-heals the next time anything triggers a live resolution
			// for this (album, viewer) pair elsewhere (e.g. a v2 root load).
			$cover_ids[] = $cached_covers[$smart_album->get_id()] ?? null;
			// Smart albums are built-in/system-wide — no real owner.
			$owner_ids[] = '0';
		}

		return new AlbumCategoryListResource(ids: $ids, titles: $titles, cover_ids: $cover_ids, owner_ids: $owner_ids);
	}

	// ── GET /Albums/tags ─────────────────────────────────────────────

	public function tags(GetAlbumCategoryRequest $request): AlbumCategoryListResource
	{
		/** @var User|null $user */
		$user = Auth::user();
		$sorting = AlbumSortingCriterion::createDefault();

		$key = $this->cache_key_provider->tagAlbumsListingKey($user?->id, $sorting);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->tagAlbumsListingTag(),
				$this->cache_key_provider->userTag($user?->id),
				$this->cache_key_provider->albumListingGlobalTag(),
			],
			fn (): AlbumCategoryListResource => $this->queryTags($user, $sorting),
			ttl: $ttl,
		);
	}

	private function queryTags(?User $user, AlbumSortingCriterion $sorting): AlbumCategoryListResource
	{
		$query = $this->album_query_policy->applyVisibilityFilter(TagAlbum::query(), $user);
		(new SortingDecorator($query))->orderBy($sorting->column, $sorting->order)->applyOrdering();

		$rows = $query
			->select(['tag_albums.id', 'base_albums.title', 'tag_albums.cover_id', 'base_albums.owner_id'])
			->toBase()
			->get();

		return $this->toCategoryListResource($rows);
	}

	// ── GET /Albums/tags/rights ──────────────────────────────────────

	public function tagsRights(GetAlbumCategoryRequest $request): AlbumCategoryRightsResource
	{
		/** @var User|null $user */
		$user = Auth::user();
		$is_admin = $user?->may_administrate === true;

		$query = $this->album_query_policy->applyVisibilityFilter(TagAlbum::query(), $user);

		if ($is_admin) {
			$ids = $query->select(['tag_albums.id'])->toBase()->pluck('id')->all();
			$count = count($ids);

			return new AlbumCategoryRightsResource(
				ids: $ids,
				grants_edit: array_fill(0, $count, true),
				grants_download: array_fill(0, $count, true),
				grants_delete: array_fill(0, $count, true),
			);
		}

		$this->album_query_policy->joinSubComputedAccessPermissions($query, 'tag_albums.id', 'left', 'grants_', true, $user);

		$rows = $query
			->select(['tag_albums.id'])
			->selectRaw('MAX(grants_computed_access_permissions.grants_edit) as grants_edit')
			->selectRaw('MAX(grants_computed_access_permissions.grants_download) as grants_download')
			->selectRaw('MAX(grants_computed_access_permissions.grants_delete) as grants_delete')
			->groupBy('tag_albums.id')
			->toBase()
			->get();

		$ids = [];
		$grants_edit = [];
		$grants_download = [];
		$grants_delete = [];
		foreach ($rows as $row) {
			$ids[] = $row->id;
			$grants_edit[] = filter_var($row->grants_edit, FILTER_VALIDATE_BOOLEAN);
			$grants_download[] = filter_var($row->grants_download, FILTER_VALIDATE_BOOLEAN);
			$grants_delete[] = filter_var($row->grants_delete, FILTER_VALIDATE_BOOLEAN);
		}

		return new AlbumCategoryRightsResource(ids: $ids, grants_edit: $grants_edit, grants_download: $grants_download, grants_delete: $grants_delete);
	}

	// ── GET /Albums/persons ──────────────────────────────────────────

	public function persons(GetScopedAlbumsRequest $request): AlbumCategoryListResource
	{
		/** @var User|null $user */
		$user = Auth::user();
		$scope = $request->scope();
		$sorting = AlbumSortingCriterion::createDefault();

		if (!$this->config_manager->getValueAsBool('ai_vision_face_enabled')) {
			return new AlbumCategoryListResource(ids: [], titles: [], cover_ids: [], owner_ids: []);
		}

		$key = $this->cache_key_provider->personAlbumsListingKey($user?->id, $sorting, $scope);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->personAlbumsListingTag(),
				$this->cache_key_provider->userTag($user?->id),
				$this->cache_key_provider->albumListingGlobalTag(),
			],
			fn (): AlbumCategoryListResource => $this->queryPersons($user, $scope, $sorting),
			ttl: $ttl,
		);
	}

	private function queryPersons(?User $user, AlbumListingScope $scope, AlbumSortingCriterion $sorting): AlbumCategoryListResource
	{
		$query = $this->album_query_policy->applyVisibilityFilter(PersonAlbum::query(), $user);
		$this->applyScopePredicate($query, $scope, $user);
		(new SortingDecorator($query))->orderBy($sorting->column, $sorting->order)->applyOrdering();

		$rows = $query
			->select(['person_albums.id', 'base_albums.title', 'base_albums.owner_id'])
			->toBase()
			->get();

		$ids = [];
		$titles = [];
		$cover_ids = [];
		$owner_ids = [];
		foreach ($rows as $row) {
			$ids[] = $row->id;
			$titles[] = $row->title;
			// PersonAlbum carries no cover_id column at all — resolving one
			// live would require a photos query this flat listing
			// deliberately never runs.
			$cover_ids[] = null;
			$owner_ids[] = (string) $row->owner_id;
		}

		return new AlbumCategoryListResource(ids: $ids, titles: $titles, cover_ids: $cover_ids, owner_ids: $owner_ids);
	}

	// ── GET /Albums/pinned ───────────────────────────────────────────

	public function pinned(GetScopedAlbumsRequest $request): AlbumCategoryListResource
	{
		/** @var User|null $user */
		$user = Auth::user();
		$scope = $request->scope();
		$pinned_col = $this->config_manager->getValueAsEnum('sorting_pinned_albums_col', ColumnSortingType::class);
		$pinned_order = $this->config_manager->getValueAsEnum('sorting_pinned_albums_order', OrderSortingType::class);

		$key = $this->cache_key_provider->pinnedAlbumsListingKey($user?->id, $pinned_col, $pinned_order, $scope);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->pinnedAlbumsListingTag(),
				$this->cache_key_provider->userTag($user?->id),
				$this->cache_key_provider->albumListingGlobalTag(),
			],
			fn (): AlbumCategoryListResource => $this->queryPinned($user, $scope, $pinned_col, $pinned_order),
			ttl: $ttl,
		);
	}

	private function queryPinned(?User $user, AlbumListingScope $scope, ?ColumnSortingType $pinned_col, ?OrderSortingType $pinned_order): AlbumCategoryListResource
	{
		$query = $this->album_query_policy->applyVisibilityFilter(
			Album::query()->joinSub(DB::table('base_albums')->select(['id', 'is_pinned'])->where('is_pinned', '=', true), 'pinned', 'pinned.id', '=', 'albums.id'),
			$user
		);
		$this->applyScopePredicate($query, $scope, $user);
		(new SortingDecorator($query))->orderBy($pinned_col ?? ColumnSortingType::CREATED_AT, $pinned_order ?? OrderSortingType::ASC)->applyOrdering();

		$rows = $query
			->select([
				'albums.id',
				'base_albums.title',
				'albums.cover_id',
				'albums.auto_cover_id_max_privilege',
				'albums.auto_cover_id_least_privilege',
				'base_albums.owner_id',
			])
			->toBase()
			->get();

		return $this->toCategoryListResource($rows, resolve_cover: true, user: $user);
	}

	// ── Shared helpers ───────────────────────────────────────────────

	/**
	 * `own` = `owner_id = $user->id`; `shared` = `owner_id != $user->id`
	 * (unfiltered for a guest, who has no "own" albums). Always one flat,
	 * ungrouped list either way (NG9) — never a `GROUP BY owner_id`.
	 *
	 * @param Builder<Album|PersonAlbum> $query
	 */
	private function applyScopePredicate(Builder $query, AlbumListingScope $scope, ?User $user): void
	{
		if ($scope === AlbumListingScope::OWN) {
			// GetScopedAlbumsRequest guarantees $user !== null whenever OWN
			// is reachable.
			$query->where('base_albums.owner_id', '=', $user->id);
		} elseif ($user !== null) {
			$query->where('base_albums.owner_id', '!=', $user->id);
		}
	}

	/**
	 * @param Collection<int,object{id:string,title:string,cover_id:?string,owner_id:int,auto_cover_id_max_privilege?:?string,auto_cover_id_least_privilege?:?string}> $rows
	 */
	private function toCategoryListResource(Collection $rows, bool $resolve_cover = false, ?User $user = null): AlbumCategoryListResource
	{
		$ids = [];
		$titles = [];
		$cover_ids = [];
		$owner_ids = [];
		foreach ($rows as $row) {
			$ids[] = $row->id;
			$titles[] = $row->title;
			$cover_ids[] = $resolve_cover ? AlbumListController::resolveCoverId($row, $user) : $row->cover_id;
			$owner_ids[] = (string) $row->owner_id;
		}

		return new AlbumCategoryListResource(ids: $ids, titles: $titles, cover_ids: $cover_ids, owner_ids: $owner_ids);
	}
}
