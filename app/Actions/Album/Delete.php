<?php

namespace App\Actions\Album;

use AccessControl;
use App\Actions\Album\Extensions\Ancestors;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Support\Collection as BaseCollection;

class Delete extends UpdateTakestamps
{
	use Ancestors;

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

			return $no_error;
		}

		$albums = Album::whereIn('id', explode(',', $albumIDs))->get();

		$ancestors = new BaseCollection();
		foreach ($albums as $album) {
			$no_error &= $album->predelete();

			//? We only need to update parents if node is not a root and if the min & max are set.
			if (!$album->isRoot() && $album->min_takestamp != null && $album->max_takestamp != null) {
				$ancestors->push(['_lft' => $album->_lft, '_rgt' => $album->_rgt, 'min' => $album->min_takestamp, 'max' => $album->max_takestamp]);
			}

			//! We break the tree (because delete() is broken see https://github.com/lazychaser/laravel-nestedset/issues/485)
			Album::where('_lft', '>', $album->_lft)->where('_rgt', '<=', $album->_rgt)->delete();

			$no_error &= $album->delete();
		}

		//! Tree is still broken, but we need to query the Ancestors Id here before fixing the tree.
		$ancestors = $this->getAncestorsOutdated($ancestors)->pluck('id');

		//? We fix the tree :)
		if (Album::isBroken()) {
			Album::fixTree();
		}

		$ancestors = Album::whereIn('id', $ancestors)->get();
		$ancestors->each(fn ($ancestor, $_) => $this->singleAndSave($ancestor));

		return $no_error;
	}
}
