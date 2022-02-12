<?php

namespace App\Actions\Album;

use App\Exceptions\ModelDBException;
use App\Models\Album;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Move extends Action
{
	/**
	 * Moves the given albums into the target.
	 *
	 * @param Album|null $targetAlbum
	 * @param Collection $albums
	 *
	 * @throws ModelNotFoundException
	 * @throws ModelDBException
	 */
	public function do(?Album $targetAlbum, Collection $albums): void
	{
		// Move source albums into target
		if ($targetAlbum) {
			/** @var Album $album */
			foreach ($albums as $album) {
				// Don't set attribute `parent_id` manually, but use specialized
				// methods of the nested set `NodeTrait` to keep the enumeration
				// of the tree consistent
				// `appendNode` also internally calls `save` on the model
				$targetAlbum->appendNode($album);
			}
			$targetAlbum->fixOwnershipOfChildren();
		} else {
			/** @var Album $album */
			foreach ($albums as $album) {
				// Don't set attribute `parent_id` manually, but use specialized
				// methods of the nested set `NodeTrait` to keep the enumeration
				// of the tree consistent
				$album->saveAsRoot();
			}
		}
	}
}
