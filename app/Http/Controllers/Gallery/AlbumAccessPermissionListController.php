<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Assets\DbBool;
use App\Http\Requests\Gallery\AlbumAccessPermissionListRequest;
use App\Http\Resources\V3\AlbumAccessPermissionResource;
use App\Models\Album;
use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Serves `GET /api/v3/Albums::accessPermissions`: a flat, Struct-of-Arrays
 * listing of every real album together with its named (user/group) access
 * permissions, for the bulk-share admin page. Admins see every album;
 * non-admins see only albums they own.
 */
class AlbumAccessPermissionListController extends Controller
{
	public function index(AlbumAccessPermissionListRequest $request): AlbumAccessPermissionResource
	{
		/** @var User $user */
		$user = Auth::user();

		return $this->buildResource($this->queryRows($user));
	}

	/**
	 * @return Collection<int,object{album_id:string,album_title:string,_lft:string,_rgt:string,owner_id:string,owner_name:string,permission_id:?string,user_id:?string,user_name:?string,group_id:?string,group_name:?string,grants_full_photo_access:?string,grants_download:?string,grants_upload:?string,grants_edit:?string,grants_delete:?string}>
	 */
	private function queryRows(User $user): Collection
	{
		// Column-narrowed subqueries, not raw table joins: `users` in
		// particular carries password hashes/tokens/settings that have no
		// reason to pass through the join. Mirrors the convention already
		// used by AlbumQueryPolicy::joinBaseAlbumOwnerId()/joinBaseAlbumBulkEditFields().
		$base_albums_sub = DB::table('base_albums')->select(['id', 'owner_id', 'title']);
		$owner_users_sub = DB::table('users')->select(['id', 'username', 'display_name']);
		$perm_users_sub = DB::table('users')->select(['id', 'username', 'display_name']);

		$query = Album::query()
			->select([
				'albums.id as album_id',
				'base_albums.title as album_title',
				'albums._lft',
				'albums._rgt',
				'base_albums.owner_id',
				DB::raw('COALESCE(owner_users.display_name, owner_users.username) as owner_name'),
				'access_permissions.id as permission_id',
				'access_permissions.user_id',
				DB::raw('COALESCE(perm_users.display_name, perm_users.username) as user_name'),
				'access_permissions.user_group_id as group_id',
				'user_groups.name as group_name',
				'access_permissions.grants_full_photo_access',
				'access_permissions.grants_download',
				'access_permissions.grants_upload',
				'access_permissions.grants_edit',
				'access_permissions.grants_delete',
			])
			->leftJoinSub($base_albums_sub, 'base_albums', 'base_albums.id', '=', 'albums.id')
			->leftJoinSub($owner_users_sub, 'owner_users', 'owner_users.id', '=', 'base_albums.owner_id')
			->leftJoin('access_permissions', function (JoinClause $join): void {
				// The `user_id`/`user_group_id` filter MUST live in the ON
				// clause (not a WHERE) so an album whose only permission row
				// is the public/link one (or which has none at all) still
				// comes through via the LEFT JOIN, with null permission
				// columns, instead of being dropped or losing its row.
				$join->on('access_permissions.base_album_id', '=', 'albums.id')
					->where(fn ($q) => $q->whereNotNull('access_permissions.user_id')
						->orWhereNotNull('access_permissions.user_group_id'));
			})
			->leftJoinSub($perm_users_sub, 'perm_users', 'perm_users.id', '=', 'access_permissions.user_id')
			->leftJoin('user_groups', 'user_groups.id', '=', 'access_permissions.user_group_id');

		if (!$user->may_administrate) {
			$query->where('base_albums.owner_id', '=', $user->id);
		}

		return $query->orderBy('albums._lft', 'asc')->orderBy('access_permissions.id', 'asc')->toBase()->get();
	}

	/**
	 * @param Collection<int,object{album_id:string,album_title:string,_lft:string,_rgt:string,owner_id:string,owner_name:string,permission_id:?string,user_id:?string,user_name:?string,group_id:?string,group_name:?string,grants_full_photo_access:?string,grants_download:?string,grants_upload:?string,grants_edit:?string,grants_delete:?string}> $rows
	 */
	private function buildResource(Collection $rows): AlbumAccessPermissionResource
	{
		$album_ids = [];
		$album_titles = [];
		$lft = [];
		$rgt = [];
		$owner_ids = [];
		$owner_names = [];
		$permission_ids = [];
		$user_ids = [];
		$user_names = [];
		$group_ids = [];
		$group_names = [];
		$grants_full_photo_accesses = [];
		$grants_downloads = [];
		$grants_uploads = [];
		$grants_edits = [];
		$grants_deletes = [];

		foreach ($rows as $row) {
			$has_permission = $row->permission_id !== null;

			$album_ids[] = $row->album_id;
			$album_titles[] = $row->album_title;
			$lft[] = (int) $row->_lft;
			$rgt[] = (int) $row->_rgt;
			$owner_ids[] = (int) $row->owner_id;
			$owner_names[] = $row->owner_name;
			$permission_ids[] = $has_permission ? (int) $row->permission_id : null;
			$user_ids[] = $row->user_id !== null ? (int) $row->user_id : null;
			$user_names[] = $row->user_name;
			$group_ids[] = $row->group_id !== null ? (int) $row->group_id : null;
			$group_names[] = $row->group_name;
			$grants_full_photo_accesses[] = $has_permission ? DbBool::parse($row->grants_full_photo_access) : null;
			$grants_downloads[] = $has_permission ? DbBool::parse($row->grants_download) : null;
			$grants_uploads[] = $has_permission ? DbBool::parse($row->grants_upload) : null;
			$grants_edits[] = $has_permission ? DbBool::parse($row->grants_edit) : null;
			$grants_deletes[] = $has_permission ? DbBool::parse($row->grants_delete) : null;
		}

		return new AlbumAccessPermissionResource(
			album_ids: $album_ids,
			album_titles: $album_titles,
			lft: $lft,
			rgt: $rgt,
			owner_ids: $owner_ids,
			owner_names: $owner_names,
			permission_ids: $permission_ids,
			user_ids: $user_ids,
			user_names: $user_names,
			group_ids: $group_ids,
			group_names: $group_names,
			grants_full_photo_accesses: $grants_full_photo_accesses,
			grants_downloads: $grants_downloads,
			grants_uploads: $grants_uploads,
			grants_edits: $grants_edits,
			grants_deletes: $grants_deletes,
		);
	}
}
