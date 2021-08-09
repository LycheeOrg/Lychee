<?php

namespace App\Actions\Album;

use App\Models\Album;
use App\Models\Logs;

class Move extends Action
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
			$album_master = $this->albumFactory->findOrFail($albumID);

			if ($album_master->is_smart()) {
				Logs::error(__METHOD__, __LINE__, 'Move is not possible on smart albums');

				return false;
			}
		} else {
			$albumID = null;
		}

		$albums = Album::query()->whereIn('id', $albumIDs)->get();
		$no_error = true;

		foreach ($albums as $album) {
			$album->parent_id = $albumID;
			$no_error &= $album->save();
		}
		// Tree should be updated by itself here.

		if ($no_error && $album_master !== null) {
			// update owner
			$album_master->descendants()->update(['owner_id' => $album_master->owner_id]);
			$album_master->all_photos()->update(['photos.owner_id' => $album_master->owner_id]);
		}

		return $no_error;
	}
}
