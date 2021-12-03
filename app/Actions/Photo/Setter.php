<?php

namespace App\Actions\Photo;

use App\Models\Photo;

/**
 * This class is used to set a property of a SINGLE photo.
 * As a result, the do function takes as input a photoID.
 *
 * do will crash if photoID is not correct, throwing an exception Model not found.
 * This is intended behaviour.
 */
class Setter
{
	public $property;

	public function do(string $photoID, string $value): bool
	{
		/** @var Photo $photo */
		$photo = Photo::query()->findOrFail($photoID);

		return $this->execute($photo, $value);
	}

	public function execute(Photo $photo, $value): bool
	{
		$photo->{$this->property} = $value;

		return $photo->save();
	}
}
