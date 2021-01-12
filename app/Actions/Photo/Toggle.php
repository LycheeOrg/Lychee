<?php

namespace App\Actions\Photo;

use App\Models\Photo;

/**
 * This class is used to toggle a boolean property of a SINGLE photo.
 * As a result, the do function takes as input a photoID.
 *
 * do will crash if photoID is not correct, throwing an exception Model not found.
 * This is intended behaviour.
 */
class Toggle
{
	public $property;

	public function do(string $photoID): bool
	{
		$photo = Photo::findOrFail($photoID);

		return $this->execute($photo);
	}

	public function execute(Photo $photo): bool
	{
		$photo->{$this->property} = $photo->{$this->property} != 1 ? 1 : 0;

		return $photo->save();
	}
}
