<?php

namespace App\Actions\Photo;

use App\Models\Photo;

class Setter
{
	public $property;

	public function do(string $photoID, string $value): bool
	{
		$photo = Photo::findOrFail($photoID);

		return $this->execute($photo, $value);
	}

	public function execute(Photo $photo, $value): bool
	{
		$photo->{$this->property} = $value;

		return $photo->save();
	}
}
