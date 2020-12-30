<?php

namespace App\Actions\Album;

use App\Models\Album;
use App\Models\Logs;

class Move extends UpdateTakestamps
{
	/**
	 * @param string $albumID
	 *
	 * @return bool
	 */
	public function do(string $albumID, array $albumIDs): bool
	{
		$album_master = null;
		// $albumID = 0 is root
		// ! check type
		if ($albumID != 0) {
			$album_master = $this->albumFactory->make($albumID);

			if ($album_master->is_smart()) {
				Logs::error(__METHOD__, __LINE__, 'Move possible on smart albums');

				return false;
			}
		} else {
			$albumID = null;
		}

		$albums = Album::whereIn('id', $albumIDs)->get();
		$no_error = true;
		$oldParentID = null;

		foreach ($albums as $album) {
			$oldParentID = $album->parent_id;
			$album->parent_id = $albumID;
			$no_error &= $album->save();
		}

		if ($no_error && $oldParentID !== null) {
			$oldParentAlbum = $this->albumFactory->make($oldParentID);
			// update takestamps of old place
			$no_error &= $this->singleAndSave($oldParentAlbum);
		}

		if ($no_error && $album_master !== null) {
			// updat owner
			$album_master->descendants()->update(['owner_id' => $album_master->owner_id]);
			$album_master->get_all_photos()->update(['photos.owner_id' => $album_master->owner_id]);

			// update takestamps parent of new place
			$no_error &= $this->singleAndSave($album_master);
		}

		return $no_error;
	}
}
