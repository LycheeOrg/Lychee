<?php

namespace App\Actions\Album;

use App\Models\Album;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;

class Merge extends Action
{
	/**
	 * @param string $albumID
	 *
	 * @return bool
	 */
	public function do(string $albumID, array $albumIDs): bool
	{
		$album_master = $this->albumFactory->findOrFail($albumID);
		if ($album_master->is_smart()) {
			Logs::error(__METHOD__, __LINE__, 'Merge is not possible on smart albums');

			return false;
		}

		$no_error = true;
		// Merge Photos
		if (DB::table('photos')->whereIn('album_id', $albumIDs)->count() > 0) {
			$no_error &= Photo::whereIn('album_id', $albumIDs)->update(['album_id' => $album_master->id]);
		}

		// Merge Sub-albums
		// ! we have to do it via Model::save() in order to not break the tree
		$albums = Album::whereIn('parent_id', $albumIDs)->get();
		foreach ($albums as $album) {
			$album->parent_id = $album_master->id;
			$album->save();
		}

		// now we delete the albums
		// ! we have to do it via Model::delete() in order to not break the tree
		$albums = Album::whereIn('id', $albumIDs)->get();
		foreach ($albums as $album) {
			$album->delete();
		}

		if (Album::isBroken()) {
			$errors = Album::countErrors();
			$sum = $errors['oddness'] + $errors['duplicates'] + $errors['wrong_parent'] + $errors['missing_parent'];
			Logs::warning(__FUNCTION__, __LINE__, 'Tree is broken with ' . $sum . ' errors.');
			Album::fixTree();
			Logs::notice(__FUNCTION__, __LINE__, 'Tree has been fixed.');
		}

		$album_master->descendants()->update(['owner_id' => $album_master->owner_id]);
		$album_master->get_all_photos()->update(['photos.owner_id' => $album_master->owner_id]);

		return $no_error;
	}
}
