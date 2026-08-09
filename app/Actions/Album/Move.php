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
use App\Exceptions\ModelDBException;
use App\Models\Album;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class Move
{
	/**
	 * Moves the given albums into the target.
	 *
	 * @throws ModelNotFoundException
	 * @throws ModelDBException
	 */
	public function do(?Album $target_album, Collection $albums): void
	{
		/** @var Collection<int,string|null> $old_parent_ids */
		$old_parent_ids = $albums->map(fn (Album $album) => $album->parent_id)->unique();

		// Move source albums into target
		if ($target_album !== null) {
			/** @var Album $album */
			foreach ($albums as $album) {
				$has_descendants = $album->_rgt - $album->_lft > 1;

				// Don't set attribute `parent_id` manually, but use specialized
				// methods of the nested set `NodeTrait` to keep the enumeration
				// of the tree consistent
				// `appendNode` also internally calls `save` on the model
				$target_album->appendNode($album);

				AlbumSaved::dispatch($album);
				if ($has_descendants) {
					AlbumListingCacheFlushRequested::dispatch();
				}
			}
			$target_album->fixOwnershipOfChildren();
		} else {
			/** @var Album $album */
			foreach ($albums as $album) {
				$has_descendants = $album->_rgt - $album->_lft > 1;

				// Don't set attribute `parent_id` manually, but use specialized
				// methods of the nested set `NodeTrait` to keep the enumeration
				// of the tree consistent
				$album->saveAsRoot();

				AlbumSaved::dispatch($album);
				if ($has_descendants) {
					AlbumListingCacheFlushRequested::dispatch();
				}
			}
		}

		foreach ($old_parent_ids as $old_parent_id) {
			AlbumChildrenChanged::dispatch($old_parent_id);
		}
	}
}