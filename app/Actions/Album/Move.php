<?php

namespace App\Actions\Album;

use App\Actions\Album\Extensions\Ancestors;
use App\Models\Album;
use App\Models\Logs;
use DebugBar;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;

class Move extends UpdateTakestamps
{
	use Ancestors;

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
				Logs::error(__METHOD__, __LINE__, 'Move is not possible on smart albums');

				return false;
			}
		} else {
			$albumID = null;
		}

		$albums = Album::whereIn('id', $albumIDs)->get();
		$no_error = true;
		$oldParentID = new BaseCollection();

		foreach ($albums as $album) {
			if ($album->parent_id != null && $album->min_takestamp != null && $album->max_takestamp != null) {
				$oldParentID->push([$album->parent_id, $album->min_takestamp, $album->max_takestamp]);
			}

			$album->parent_id = $albumID;
			$no_error &= $album->save();
		}
		// Tree should be updated by itself here.

		$sql = Album::select('_lft', '_rgt', 'min_takestamp', 'max_takestamp')
			->where(DB::raw('0')); // initialize with 0.

		$oldParentID->eachSpread(function ($id, $min, $max) use (&$sql) {
			$sql = $sql->orWhere(fn ($q) => $q
				// this smartly select all the ancestors
				->where('id', '=', $id)
				// this smartly select only the one that need to be updated:
				// min is greater OR EQUAL than the min of the deleted album
				// max is smaller OR EQUAL than the max of the deleted album
				//? the EQUAL is the condition we are interested in, bigger/smaller is just for safety
				->where(fn ($q) => $q
					->where('min_takestamp', '>=', $min)
					->orWhere('max_takestamp', '<=', $max)));
		});
		$ancestorsList = $sql->get()->pluck(['_lft', '_rgt', 'min_takestamp', 'max_takestamp']);
		DebugBar::notice($ancestorsList);

		$ancestors = $this->getAncestorsOutdated($ancestorsList, '>=', '<=');
		$ancestors->each(fn ($ancestor, $_) => $this->singleAndSave($ancestor));

		if ($no_error && $album_master !== null) {
			// updat owner
			$album_master->descendants()->update(['owner_id' => $album_master->owner_id]);
			$album_master->get_all_photos()->update(['photos.owner_id' => $album_master->owner_id]);

			// update takestamps parent of new place
			$no_error &= $this->singleAndSave($album_master);

			// propagate to ancestors (only to the necessary ones)
			$this->ancestors($album_master);
		}

		return $no_error;
	}
}
