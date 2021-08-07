<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

class AlbumOld extends Model
{
	/**
	 * Before calling delete() to remove the album from the database
	 * we need to go through each sub album and delete it.
	 * Idem we also delete each pictures inside an album (recursively).
	 *
	 * @return bool|null
	 *
	 * @throws Exception
	 */
	public function predelete()
	{
		$no_error = true;
		$photos = $this->get_all_photos()->get();
		foreach ($photos as $photo) {
			$no_error &= $photo->predelete();
			$no_error &= $photo->delete();
		}

		return $no_error;
	}

	/**
	 * Return the full path of the album consisting of all its parents' titles.
	 *
	 * @return string
	 */
	public static function getFullPath($album)
	{
		$title = [$album->title];
		$parentId = $album->parent_id;
		while ($parentId) {
			$parent = Album::find($parentId);
			array_unshift($title, $parent->title);
			$parentId = $parent->parent_id;
		}

		return implode('/', $title);
	}
}
