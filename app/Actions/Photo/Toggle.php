<?php

namespace App\Actions\Photo;

use App\Exceptions\ModelDBException;
use App\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * This class is used to toggle a boolean property of a SINGLE photo.
 * As a result, the do function takes as input a photoID.
 *
 * do will crash if photoID is not correct, throwing an exception Model not found.
 * This is intended behaviour.
 */
class Toggle
{
	public string $property;

	/**
	 * @throws ModelDBException
	 * @throws ModelNotFoundException
	 */
	public function do(string $photoID): bool
	{
		/** @var Photo $photo */
		$photo = Photo::query()->findOrFail($photoID);

		return $this->execute($photo);
	}

	/**
	 * @throws ModelDBException
	 */
	public function execute(Photo $photo): bool
	{
		$photo->{$this->property} = !($photo->{$this->property});

		return $photo->save();
	}
}
