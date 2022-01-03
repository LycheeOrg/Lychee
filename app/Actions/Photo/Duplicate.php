<?php

namespace App\Actions\Photo;

use App\Models\Photo;
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
	 */
	public function do(array $photoIds, ?string $albumID): Collection
	{
		$duplicates = new Collection();
		$photos = Photo::query()
			->with(['size_variants'])
			->whereIn('id', $photoIds)->get();

		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$duplicate = $photo->replicate();
			$duplicate->album_id = $albumID;
			$duplicate->save();
			$duplicates->add($duplicate);
		}

		return $duplicates;
	}
}
