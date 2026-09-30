<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Sharing;

use App\Constants\AccessPermissionConstants as APC;
use App\Models\AccessPermission;
use App\Models\Album;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

final class Propagate
{
	/**
	 * Update all descendants with the current album access permissions.
	 * This is run in a DB transaction for safety.
	 *
	 * @param Album $album
	 *
	 * @return array<int,string> the ids of the descendants whose permissions were written
	 */
	public function update(Album $album): array
	{
		if (!App::runningUnitTests()) {
			// @codeCoverageIgnoreStart
			return DB::transaction(fn () => $this->applyUpdate($album));
			// @codeCoverageIgnoreEnd
		}

		return $this->applyUpdate($album);
	}

	/**
	 * Apply the current album access permissions to all descendants.
	 *
	 * @param Album $album
	 *
	 * @return array<int,string> the descendant ids
	 */
	private function applyUpdate(Album $album): array
	{
		// for each descendant, create a new permission if it does not exist.
		// or update the existing permission.
		$descendants = $album->descendants()->getQuery()->select('id')->pluck('id');
		$permissions = $album->access_permissions()->where(fn ($q) => $q
			->whereNotNull(APC::USER_ID)
			->orWhereNotNull(APC::USER_GROUP_ID)
		)->get();

		// This is super inefficient.
		// It would be better to do it in a single query...
		// But how?
		$descendants->each(function (string $descendant, int|string $idx) use ($permissions): void {
			$permissions->each(function (AccessPermission $permission) use ($descendant): void {
				$perm = AccessPermission::updateOrCreate([
					APC::BASE_ALBUM_ID => $descendant,
					APC::USER_ID => $permission->user_id,
					APC::USER_GROUP_ID => $permission->user_group_id,
				], [
					APC::GRANTS_FULL_PHOTO_ACCESS => $permission->grants_full_photo_access,
					APC::GRANTS_DOWNLOAD => $permission->grants_download,
					APC::GRANTS_UPLOAD => $permission->grants_upload,
					APC::GRANTS_EDIT => $permission->grants_edit,
					APC::GRANTS_DELETE => $permission->grants_delete,
					APC::GRANTS_MOVE => $permission->grants_move,
				]);
				$perm->save();
			});
		});

		return $descendants->all();
	}

	/**
	 * Overwrite all descendants with the current album access permissions.
	 *
	 * @param Album $album
	 *
	 * @return array<int,string> the ids of the descendants whose permissions were rewritten
	 */
	public function overwrite(Album $album): array
	{
		if (!App::runningUnitTests()) {
			// @codeCoverageIgnoreStart
			return DB::transaction(fn () => $this->applyOverwrite($album));
			// @codeCoverageIgnoreEnd
		}

		return $this->applyOverwrite($album);
	}

	/**
	 * Apply the overwrite of all descendants with the current album access permissions.
	 *
	 * @param Album $album
	 *
	 * @return array<int,string> the descendant ids
	 */
	private function applyOverwrite(Album $album): array
	{
		// override permission for all descendants albums.
		// Faster done by:
		// 1. clearing all the permissions.
		// 2. applying the new permissions.

		DB::table(APC::ACCESS_PERMISSIONS)
			->where(fn ($q) => $q
				->whereNotNull(APC::USER_ID)
				->orWhereNotNull(APC::USER_GROUP_ID)
			)
			->whereIn(
				'base_album_id',
				DB::table('albums')
					->select('id')
					->where('_lft', '>', $album->_lft)
					->where('_rgt', '<', $album->_rgt)
			)
			->delete();

		$descendant_ids = DB::table('albums')
			->select('id')
			->where('_lft', '>', $album->_lft)
			->where('_rgt', '<', $album->_rgt)
			->pluck('id');

		// The DELETE above revoked every own permission the descendants had:
		// drop the cached covers those permissions may have produced before
		// re-inserting the ancestor's (see PurgeAlbumUserThumbs).
		resolve(PurgeAlbumUserThumbs::class)->forBaseAlbums($descendant_ids);

		$access_permissions = $album->access_permissions()->where(fn ($q) => $q
			->whereNotNull(APC::USER_ID)
			->orWhereNotNull(APC::USER_GROUP_ID)
		)->get();

		$new_perm = $access_permissions->reduce(
			fn (?array $acc, AccessPermission $permission) => array_merge(
				$acc ?? [],
				$descendant_ids->map(
					fn ($descendant_id) => [
						APC::BASE_ALBUM_ID => $descendant_id,
						APC::USER_ID => $permission->user_id,
						APC::USER_GROUP_ID => $permission->user_group_id,
						APC::GRANTS_FULL_PHOTO_ACCESS => $permission->grants_full_photo_access,
						APC::GRANTS_DOWNLOAD => $permission->grants_download,
						APC::GRANTS_UPLOAD => $permission->grants_upload,
						APC::GRANTS_EDIT => $permission->grants_edit,
						APC::GRANTS_DELETE => $permission->grants_delete,
						APC::GRANTS_MOVE => $permission->grants_move,
					]
				)->all()
			)
		);

		DB::table(APC::ACCESS_PERMISSIONS)->insert($new_perm);

		return $descendant_ids->all();
	}
}