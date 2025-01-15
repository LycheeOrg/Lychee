<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Models\AccessPermission;
use App\Models\Extensions\BaseAlbum;
use Illuminate\Support\Facades\Hash;

/**
 * Class SetProtectionPolicy.
 */
class SetProtectionPolicy extends Action
{
	/**
	 * @param BaseAlbum             $album
	 * @param AlbumProtectionPolicy $protectionPolicy
	 * @param bool                  $shallSetPassword
	 * @param string|null           $password
	 *
	 * @return void
	 *
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 * @throws FrameworkException
	 */
	public function do(BaseAlbum $album, AlbumProtectionPolicy $protectionPolicy, bool $shallSetPassword, ?string $password): void
	{
		$album->is_nsfw = $protectionPolicy->is_nsfw;
		$album->save();

		$active_permissions = $album->public_permissions();

		if (!$protectionPolicy->is_public) {
			$active_permissions?->delete();

			return;
		}

		// Security attributes of the album itself independent of a particular user
		$active_permissions ??= new AccessPermission();
		$active_permissions->is_link_required = $protectionPolicy->is_link_required;
		$active_permissions->grants_full_photo_access = $protectionPolicy->grants_full_photo_access;
		$active_permissions->grants_download = $protectionPolicy->grants_download;
		$active_permissions->base_album_id = $album->id;

		// $album->public_permissions = $active_permissions;

		// Set password if provided
		if ($shallSetPassword) {
			// password is provided => there is a change
			if ($password !== null) {
				// password is not null => we update the value with the hash
				$active_permissions->password = Hash::make($password);
			} else {
				// we remove the password
				$active_permissions->password = null;
			}
		}
		$active_permissions->base_album_id = $album->id;
		$active_permissions->save();
	}
}
