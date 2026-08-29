<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Constants\AccessPermissionConstants as APC;
use App\Http\Requests\Album\GetAlbumChildrenRightsRequest;
use App\Http\Resources\V3\AlbumChildrenRightsResource;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Serves `GET /api/v3/Albums/{album_id}/children/rights` (Feature 061,
 * API-061-03): the permission signals a right-click/multi-select context
 * menu on one parent album's direct children needs, all at once,
 * background-fetched — zero query at the moment of any interaction event.
 */
class AlbumChildrenRightsController extends Controller
{
	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
	) {
	}

	public function index(GetAlbumChildrenRightsRequest $request): AlbumChildrenRightsResource
	{
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();

		$key = $this->cache_key_provider->albumChildrenRightsKey($album->id, $user?->id);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[$this->cache_key_provider->albumChildrenTag($album->id)],
			fn (): AlbumChildrenRightsResource => $this->queryRights($album, $user),
			ttl: $ttl,
		);
	}

	private function queryRights(Album $album, ?User $user): AlbumChildrenRightsResource
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

		// getComputedAccessPermissionSubQuery(full: true, ...) applies no
		// internal GROUP BY (FR-061-21) — a caller in multiple groups with
		// separate matching grants on the same child would otherwise
		// produce duplicate joined rows. GROUP BY + MAX() here correctly
		// OR-merges them: any matching group/user/public row granting a
		// right is enough (NFR-061-09).
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
