<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;

class Transfer extends Action
{
	/**
	 * Moves the given albums into the target.
	 *
	 * @param BaseAlbum $baseAlbum
	 * @param int       $userId
	 */
	public function do(BaseAlbum $baseAlbum, int $userId): void
	{
		$baseAlbum->owner_id = $userId;
		$baseAlbum->save();

		// No longer necessary because we transfer the ownership
		AccessPermission::query()->where('base_album_id', '=', $baseAlbum->id)->where('user_id', '=', $userId)->delete();

		// If this is an Album, we also need to fix the children and photos ownership
		if ($baseAlbum instanceof Album) {
			$baseAlbum->makeRoot();
			$baseAlbum->save();
			$baseAlbum->fixOwnershipOfChildren();
		}
	}
}
