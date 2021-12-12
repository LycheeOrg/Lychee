<?php

namespace App\Actions\Photo;

use App\Exceptions\ModelDBException;
use App\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class Duplicate
{
	/**
	 * Duplicates a set of photos.
	 *
	 * If the ID of the destination album is not given, each photo is
	 * duplicated within its current album.
	 * This implies that photos may be duplicated within different
	 * albums, if the original photos reside in different albums.
	 * If the ID of a destination album is given, then all duplicates are
	 * created in the destination album (this resembles a "copy-to" semantic).
	 * However, you cannot copy photos to the root album (whose ID equals
	 * `null`), because `null` means to duplicate photos in-place.
	 *
	 * @param string[]    $photoIds the IDs of the source photos
	 * @param string|null $albumID  the optional ID of the destination album
	 *
	 * @return Collection<Photo> the duplicates
	 *
	 * @throws ModelDBException
	 */
	public function do(array $photoIds, ?string $albumID): Collection
	{
		$duplicates = new Collection();
		try {
			$photos = Photo::query()
				->with(['size_variants'])
				->whereIn('id', $photoIds)->get();

			/** @var Photo $photo */
			foreach ($photos as $photo) {
				$duplicate = $photo->replicate();
				$duplicate->album_id = $albumID ?: $photo->album_id;
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
