<?php

namespace App\Actions\Album;

use App\Models\Album;

/**
 * This class is used to set a property of a SINGLE album.
 * As a result, the do function takes as input an albumID.
 *
 * do will crash if albumID is not correct, throwing an exception Model not found.
 * This is intended behaviour.
 */
class Setter extends Action
{
	public string $property;

	public function do(string $albumID, ?string $value): bool
	{
		$album = $this->albumFactory->findOrFail($albumID);

		return $this->execute($album, $value);
	}

	public function execute(Album $album, $value): bool
	{
		$album->{$this->property} = $value;

		return $album->save();
	}
}
