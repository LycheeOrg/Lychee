<?php

namespace App\Actions\Album;

use App\Models\Album;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;

class Tracks extends Action
{
	/**
	 * @param string $albumID the ID of the album
	 * @param mixed  $value   the value to be set
	 */
	public function set(string $albumID, mixed $value): void
	{
		$album = $this->albumFactory->findOrFail($albumID, false);
		if ($album->track_id != null) {
			Storage::delete("tracks/$album->track_id.xml");
		}

		$new_track_id = uniqid();
		Storage::putFileAs('tracks/', $value, "$new_track_id.xml");

		if (Album::query()
				->where('id', '=', $albumID)
				->update(['track_id' => $new_track_id]) !== 1
		) {
			throw new ModelNotFoundException();
		}
	}

	/**
	 * Delete the track of an album.
	 *
	 * @param string $albumID the ID of the album
	 */
	public function delete(string $albumID): void
	{
		$album = $this->albumFactory->findOrFail($albumID, false);
		if ($album->track_id == null) {
			return;
		}
		Storage::delete("tracks/$album->track_id.xml");
		if (Album::query()
				->where('id', '=', $albumID)
				->update(['track_id' => null]) !== 1
		) {
			throw new ModelNotFoundException();
		}
	}
}
