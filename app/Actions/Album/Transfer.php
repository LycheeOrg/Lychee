<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Events\AlbumChildrenChanged;
use App\Events\AlbumListingCacheFlushRequested;
use App\Events\AlbumSaved;
use App\Events\PersonAlbumSaved;
use App\Events\TagAlbumSaved;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use App\Models\PersonAlbum;
use App\Models\TagAlbum;

class Transfer
{
	/**
	 * Moves the given albums into the target.
	 */
	public function do(BaseAlbum $base_album, int $user_id): void
	{
		$base_album->owner_id = $user_id;
		$base_album->save();

		// No longer necessary because we transfer the ownership
		AccessPermission::query()->where('base_album_id', '=', $base_album->get_id())->where('user_id', '=', $user_id)->delete();

		// If this is an Album, we also need to fix the children and photos ownership
		if ($base_album instanceof Album) {
			$old_parent_id = $base_album->parent_id;
			$has_descendants = !$base_album->isLeaf();

			$base_album->makeRoot();
			$base_album->save();
			$base_album->fixOwnershipOfChildren();

			AlbumSaved::dispatch([$base_album->id], [$base_album->parent_id]);
			AlbumChildrenChanged::dispatchIf($old_parent_id !== null, [$old_parent_id]);
			AlbumListingCacheFlushRequested::dispatchIf($has_descendants);

			return;
		}

		match (true) {
			$base_album instanceof TagAlbum => TagAlbumSaved::dispatch([$base_album->id]),
			$base_album instanceof PersonAlbum => PersonAlbumSaved::dispatch($base_album),
			default => null,
		};
	}
}