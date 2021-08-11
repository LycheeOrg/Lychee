<?php

namespace App\Actions\Album;

use App\Models\Album;
use App\Models\Logs;

class Move extends Action
{
	/**
	 * Moves the given albums into the target.
	 *
	 * @param string $targetAlbumID
	 * @param array  $albumIDs
	 */
	public function do(string $targetAlbumID, array $albumIDs): void
	{
		if (empty($targetAlbumID)) {
			$targetAlbumID = null;
			$targetAlbum = null;
		} else {
			$targetAlbum = $this->albumFactory->findOrFail($targetAlbumID);
			if (!($targetAlbum instanceof Album)) {
				$msg = 'Move is only possible for real albums';
				Logs::error(__METHOD__, __LINE__, $msg);
				throw new \InvalidArgumentException($msg);
			}
		}

		$albums = Album::query()->whereIn('id', $albumIDs)->get();

		// Merge source albums to target
		// ! we have to do it via Model::save() in order to not break the tree
		foreach ($albums as $album) {
			$album->parent_id = $targetAlbumID;
			$album->save();
		}

		// Tree should be updated by itself here.

		if ($targetAlbum) {
			// update owner
			$targetAlbum->descendants()->update(['owner_id' => $targetAlbum->owner_id]);
			$targetAlbum->all_photos()->update(['owner_id' => $targetAlbum->owner_id]);
		}
	}
}
