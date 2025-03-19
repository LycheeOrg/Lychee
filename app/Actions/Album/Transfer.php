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

class Transfer
{
	/**
	 * Moves the given albums into the target.
	 *
	 * @param BaseAlbum $baseAlbum
	 * @param int       $userId
	 */
	public function do(BaseAlbum $base_album, int $user_id): void
	{
		$base_album->owner_id = $user_id;
		$base_album->save();

		// No longer necessary because we transfer the ownership
		AccessPermission::query()->where('base_album_id', '=', $base_album->id)->where('user_id', '=', $user_id)->delete();

		// If this is an Album, we also need to fix the children and photos ownership
		if ($base_album instanceof Album) {
			$base_album->makeRoot();
			$base_album->save();
			$base_album->fixOwnershipOfChildren();
		}
	}
}
