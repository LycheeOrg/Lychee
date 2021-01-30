<?php

namespace App\Actions\Album;

use AccessControl;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Support\Facades\Schema;

class Delete
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
		if ($albumIDs == 'unsorted') {
			$photos = Photo::OwnedBy(AccessControl::id())->where('album_id', '=', null)->get();

			foreach ($photos as $photo) {
				$no_error &= $photo->predelete();
				$no_error &= $photo->delete();
			}

			return $no_error;
		}

		$albums = Album::whereIn('id', explode(',', $albumIDs))->get();

		$sqlPhoto = Photo::leftJoin('albums', 'photos.album_id', '=', 'albums.id')
			->select('photos.*')
			->where(0);

		foreach ($albums as $album) {
			$sqlPhoto = $sqlPhoto->orWhere(fn ($q) => $q->where('albums._lft', '>=', $album->_lft)
				->where('albums._rgt', '<=', $album->_rgt));
		}

		$photos = $sqlPhoto->get();
		foreach ($photos as $photo) {
			$no_error &= $photo->predelete();
			$no_error &= $photo->delete();
		}

		Schema::disableForeignKeyConstraints();
		foreach ($albums as $album) {
			//! We break the tree (because delete() is broken see https://github.com/lazychaser/laravel-nestedset/issues/485)
			Album::where('_lft', '>=', $album->_lft)->where('_rgt', '<=', $album->_rgt)->delete();
		}
		Schema::enableForeignKeyConstraints();

		//? We fix the tree :)
		if (Album::isBroken()) {
			Album::fixTree();
		}

		return $no_error;
	}
}
