<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Sharing;

use App\Constants\AccessPermissionConstants as APC;
use App\Models\AccessPermission;
use App\Models\Album;
use Illuminate\Support\Collection;
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
	 * @return void
	 */
	public function update(Album $album): void
	{
		if (!App::runningUnitTests()) {
			// @codeCoverageIgnoreStart
			DB::transaction(fn () => $this->applyUpdate($album));

			return;
			// @codeCoverageIgnoreEnd
		}

		$this->applyUpdate($album);
	}

	/**
	 * Apply the current album access permissions to all descendants.
	 *
	 * @param Album $album
	 *
	 * @return void
	 */
	private function applyUpdate(Album $album): void
	{
		// for each descendant, create a new permission if it does not exist.
		// or update the existing permission.
		/** @var Collection<int,string> $descendants */
		$descendants = $album->descendants()->select('id')->pluck('id');
		$permissions = $album->access_permissions()->whereNotNull('user_id')->get();

		// This is super inefficient.
		// It would be better to do it in a single query...
		// But how?
		$descendants->each(function (string $descendant, int|string $idx) use ($permissions) {
			$permissions->each(function (AccessPermission $permission) use ($descendant) {
				$perm = AccessPermission::updateOrCreate([
					APC::BASE_ALBUM_ID => $descendant,
					APC::USER_ID => $permission->user_id,
				], [
					APC::GRANTS_FULL_PHOTO_ACCESS => $permission->grants_full_photo_access,
					APC::GRANTS_DOWNLOAD => $permission->grants_download,
					APC::GRANTS_UPLOAD => $permission->grants_upload,
					APC::GRANTS_EDIT => $permission->grants_edit,
					APC::GRANTS_DELETE => $permission->grants_delete,
				]);
				$perm->save();
			});
		});
	}

	/**
	 * Overwrite all descendants with the current album access permissions.
	 *
	 * @param Album $album
	 *
	 * @return void
	 */
	public function overwrite(Album $album): void
	{
		if (!App::runningUnitTests()) {
			// @codeCoverageIgnoreStart
			DB::transaction(fn () => $this->applyOverwrite($album));

			return;
			// @codeCoverageIgnoreEnd
		}

		$this->applyOverwrite($album);
	}

	/**
	 * Apply the overwrite of all descendants with the current album access permissions.
	 *
	 * @param Album $album
	 *
	 * @return void
	 */
	private function applyOverwrite(Album $album): void
	{
		// override permission for all descendants albums.
		// Faster done by:
		// 1. clearing all the permissions.
		// 2. applying the new permissions.

		DB::table(APC::ACCESS_PERMISSIONS)
			->whereNotNull('user_id')
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

		$access_permissions = $album->access_permissions()->whereNotNull('user_id')->get();

		$new_perm = $access_permissions->reduce(
			fn (?array $acc, AccessPermission $permission) => array_merge(
				$acc ?? [],
				$descendant_ids->map(
					fn ($descendant_id) => [
						APC::BASE_ALBUM_ID => $descendant_id,
						APC::USER_ID => $permission->user_id,
						APC::GRANTS_FULL_PHOTO_ACCESS => $permission->grants_full_photo_access,
						APC::GRANTS_DOWNLOAD => $permission->grants_download,
						APC::GRANTS_UPLOAD => $permission->grants_upload,
						APC::GRANTS_EDIT => $permission->grants_edit,
						APC::GRANTS_DELETE => $permission->grants_delete,
					]
				)->all()
			)
		);

		DB::table(APC::ACCESS_PERMISSIONS)->insert($new_perm);
	}
}
