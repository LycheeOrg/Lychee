<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album;

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
		// Move source albums into target
		if ($target_album !== null) {
			/** @var Album $album */
			foreach ($albums as $album) {
				$old_parent_id = $album->parent_id;
				// Don't set attribute `parent_id` manually, but use specialized
				// methods of the nested set `NodeTrait` to keep the enumeration
				// of the tree consistent
				// `appendNode` also internally calls `save` on the model
				$target_album->appendNode($album);
				AlbumSaved::dispatch($album);
				$this->dispatchForOldParentIfChanged($old_parent_id, $album->parent_id);
			}
			$target_album->fixOwnershipOfChildren();
		} else {
			/** @var Album $album */
			foreach ($albums as $album) {
				$old_parent_id = $album->parent_id;
				// Don't set attribute `parent_id` manually, but use specialized
				// methods of the nested set `NodeTrait` to keep the enumeration
				// of the tree consistent
				$album->saveAsRoot();
				AlbumSaved::dispatch($album);
				$this->dispatchForOldParentIfChanged($old_parent_id, $album->parent_id);
			}
		}
	}

	/**
	 * Also dispatches `AlbumSaved` for the album's *previous* parent when it
	 * actually changed, mirroring `Photo\MoveOrDuplicate`'s source/destination
	 * dispatch pattern: the old parent's set of children changed too, and its
	 * managed-cache tag (FR-052-06) would otherwise never be evicted since
	 * nothing else carries its id after the move completes.
	 */
	private function dispatchForOldParentIfChanged(?string $old_parent_id, ?string $new_parent_id): void
	{
		if ($old_parent_id === null || $old_parent_id === $new_parent_id) {
			return;
		}

		$old_parent = Album::find($old_parent_id);
		if ($old_parent !== null) {
			AlbumSaved::dispatch($old_parent);
		}
	}
}