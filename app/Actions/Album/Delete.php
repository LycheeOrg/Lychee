<?php

namespace App\Actions\Album;

use AccessControl;
use App\Models\Album;
use App\Models\Photo;

class Delete extends UpdateTakestamps
{
	/**
	 * @param string $albumID
	 *
	 * @return bool
	 */
	public function do(string $albumIDs): bool
	{
		$no_error = true;
		// root = unsorted
		if ($albumIDs == '0') {
			$photos = Photo::select_unsorted(Photo::OwnedBy(AccessControl::id()))->get();
			foreach ($photos as $photo) {
				$no_error &= $photo->predelete();
				$no_error &= $photo->delete();
			}

			return $no_error ? 'true' : 'false';
		}

		$albums = Album::whereIn('id', explode(',', $albumIDs))->get();

		/**
		 * @var Album
		 */
		$parentAlbum = null;
		foreach ($albums as $album) {
			$no_error &= $album->predelete();
			if ($parentAlbum !== null && $album->parent_id !== null) {
				$parentAlbum = $album->parent_id;
			}
			//! We break the tree (because delete() is broken see https://github.com/lazychaser/laravel-nestedset/issues/485)
			Album::where('_lft', '>', $album->_lft)->where('_rgt', '<=', $album->_rgt)->delete();
			$no_error &= $album->delete();
		}
		// We fix the tree :)
		if (Album::isBroken()) {
			Album::fixTree();
		}

		/**
		 *  We can just do that at the end, because $parentAlbum will
		 * be the same for all the albums in the case of a sub album.
		 */
		if ($parentAlbum !== null) {
			$parentAlbum = $this->albumFactory->make($parentAlbum);
			$this->singleAndSave($parentAlbum);
		}

		return $no_error;
	}
}
