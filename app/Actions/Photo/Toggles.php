<?php

namespace App\Actions\Photo;

use App\Models\Photo;

/**
 * This class toggle a boolean property of a MULTIPLE photos at the same time.
 * As a result, the do function takes as input an array containing the desired photoIDs.
 *
 * This will NOT CRASH if one of the photoID is incorrect due to the nature of the SQL query.
 */
class Toggles
{
	public string $property;

	public function do(array $photoIDs): bool
	{
		$photos = Photo::query()->whereIn('id', $photoIDs)->get();
		$no_error = true;
		foreach ($photos as $photo) {
			$photo->{$this->property} = !($photo->{$this->property});
			$no_error &= $photo->save();
		}

		return $no_error;
	}
}
