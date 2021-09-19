<?php

namespace App\Observers;

use App\Exceptions\ModelDBException;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

class PhotoObserver
{
	/**
	 * Callback for the Photo "deleting" event.
	 *
	 * This method deletes the actual media files from storage, before the last
	 * {@link Photo} which points the media files is deleted from the database.
	 *
	 * @param Photo $photo the photo to be deleted
	 *
	 * @return bool true, if the framework may continue with deletion, false otherwise
	 *
	 * @throws ModelDBException
	 */
	public function deleting(Photo $photo): bool
	{
		$keepFiles = $photo->hasDuplicate();
		if ($keepFiles) {
			Logs::notice(__METHOD__, __LINE__, $photo->id . ' is a duplicate, files are not deleted!');
		}
		// Delete all size variants
		$photo->size_variants->delete($keepFiles, $keepFiles);
		// Delete Live Photo Video file
		$success = true;
		if (!$keepFiles && !empty($photo->live_photo_short_path) && Storage::exists($photo->live_photo_short_path)) {
			$success &= Storage::delete($photo->live_photo_short_path);
		}

		return $success;
	}
}
