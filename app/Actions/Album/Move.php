<?php

namespace App\Actions\Album;

use App\Models\Album;

class Move extends Action
{
	/**
	 * Moves the given albums into the target.
	 *
	 * @param string|null $targetAlbumID
	 * @param string[]    $albumIDs
	 */
	public function do(?string $targetAlbumID, array $albumIDs): void
	{
		if (empty($targetAlbumID)) {
			$targetAlbum = null;
		} else {
			/** @var Album $targetAlbum */
			$targetAlbum = Album::query()->findOrFail($targetAlbumID);
		}

		$albums = Album::query()->whereIn('id', $albumIDs)->get();

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
