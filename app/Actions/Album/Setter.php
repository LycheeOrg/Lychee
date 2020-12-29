<?php

namespace App\Actions\Album;

use App\Models\Album;

class Setter extends Action
{
	public $property;

	public function do(string $albumID, string $value): bool
	{
		$album = $this->albumFactory->make($albumID);

		return $this->execute($album, $value);
	}

	public function execute(Album $album, $value): bool
	{
		$album->{$this->property} = $value;

		return $album->save();
	}
}
