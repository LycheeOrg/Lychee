<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Assets\DbBool;
use App\Constants\AccessPermissionConstants as APC;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\Album\GetAlbumChildrenRightsRequest;
use App\Http\Resources\V3\AlbumChildrenRightsResource;
use App\Models\AccessPermission;
use App\Models\Album;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Serves `GET /api/v3/Albums/{album_id}/children/rights` (Feature 061,
 * API-061-03): the permission signals a right-click/multi-select context
 * menu on one parent album's direct children needs, all at once,
 * background-fetched — zero query at the moment of any interaction event.
 *
 * For a {@see TagAlbum}/{@see PersonAlbum}, "children" is the same
 * "matching albums" listing {@see AlbumChildrenDataController} serves for
 * tier 2 — but `can_delete_children`/`can_move_children` are always `false`:
 * a matching-albums result has no single shared parent whose `access_permissions`
 * grants could uniformly apply, unlike a real `Album`'s direct children
 * (which always share `parent_id = album_id`). `grants_edit`/`grants_download`
 * stay per-child-meaningful — each matching album has its own real grants.
 */
class AlbumChildrenRightsController extends Controller
{
	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
		protected AlbumRepository $album_repository,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
		protected ConfigManager $config_manager,
	) {
	}

	public function index(GetAlbumChildrenRightsRequest $request): AlbumChildrenRightsResource
	{
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();

		// See AlbumChildrenDataController::index() for why the unlocked-album
		// digest must be part of this key for TagAlbum/PersonAlbum.
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

		return [$ids, $grants_edit, $grants_download];
	}

	/**
	 * Mirrors {@see \App\Policies\AlbumPolicy::canDelete()}'s parent-scoped
	 * `AccessPermission` query verbatim (`AlbumPolicy.php:296-303`),
	 * addressed directly at `$album->id` (equivalent to
	 * `$abstract_album->parent_id` there, since every returned child's
	 * parent *is* `$album->id`). {@see \App\Http\Resources\Rights\AlbumRightsResource::$can_move}
	 * already reuses this same delete gate for `can_move` — mirrored here
	 * as `can_move_children` (FR-061-20).
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
