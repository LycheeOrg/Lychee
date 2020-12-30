<?php

namespace App\Actions\Photo;

use App\Models\Photo;

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
