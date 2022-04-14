<?php

namespace App\Actions\Album;

use App\Models\Album;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Tracks extends Action
{
	/**
	 * @param string $albumID the ID of the album
	 * @param mixed  $value   the value to be set
	 */
	public function set(string $albumID, UploadedFile $value): void
	{
		$album = Album::query()->findOrFail($albumID);
		if ($album->track_short_path != null) {
			Storage::delete($album->track_short_path);
		}

		$new_track_id = sha1($value);
		Storage::putFileAs('tracks/', $value, "$new_track_id.xml");
		$short_track_path = "tracks/$new_track_id.xml";

		if (Album::query()
				->where('id', '=', $albumID)
				->update(['track_short_path' => $short_track_path]) !== 1
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
		$album = Album::query()->findOrFail($albumID);
		if ($album->track_short_path == null) {
			return;
		}
		Storage::delete($album->track_short_path);
		if (Album::query()
				->where('id', '=', $albumID)
				->update(['track_short_path' => null]) !== 1
		) {
			throw new ModelNotFoundException();
		}
	}
}
