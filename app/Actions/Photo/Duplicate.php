<?php

namespace App\Actions\Photo;

use App\Exceptions\ModelDBException;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class Duplicate
{
	/**
	 * Duplicates a set of photos.
	 *
	 * @param string[]    $photoIds the IDs of the source photos
	 * @param string|null $albumID  the ID of the destination album;
	 *                              `null` means root album
	 *
	 * @return Collection<Photo> the duplicates
	 *
	 * @throws ModelDBException
	 */
	public function do(array $photoIds, ?string $albumID): Collection
	{
		/** @var Album|null $album */
		$album = null;
		if ($albumID) {
			$album = Album::query()->findOrFail($albumID);
		}
		$duplicates = new Collection();
		try {
			$photos = Photo::query()
				->with(['size_variants'])
				->whereIn('id', $photoIds)->get();

			/** @var Photo $photo */
			foreach ($photos as $photo) {
				$duplicate = $photo->replicate();
				$duplicate->album_id = $albumID;
				if ($album) {
					$duplicate->owner_id = $album->owner_id;
				}
				$duplicate->save();
				$duplicates->add($duplicate);
			}
		} catch (\InvalidArgumentException $ignored) {
			// In theory whereIn may throw this exception,
			// but will never do so for array operands.
			return $duplicates;
		} catch (ModelNotFoundException $e) {
		}

		return $duplicates;
	}
}
