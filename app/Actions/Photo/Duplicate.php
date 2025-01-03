<?php

namespace App\Actions\Photo;

use App\Exceptions\ModelDBException;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Support\Collection;

class Duplicate
{
	/**
	 * Duplicates a set of photos.
	 *
	 * @param Collection<int,Photo> $photos the source photos
	 * @param Album|null            $album  the destination album; `null` means root album
	 *
	 * @return Collection<int,Photo> the duplicates
	 *
	 * @throws ModelDBException
	 */
	public function do(Collection $photos, ?Album $album): Collection
	{
		$duplicates = new Collection();
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$duplicate = $photo->replicate();
			$duplicate->album_id = $album?->id;
			$duplicate->setRelation('album', $album);
			if ($album !== null) {
				$duplicate->owner_id = $album->owner_id;
			}
			$duplicate->save();
			$duplicates->add($duplicate);
		}

		return $duplicates;
	}
}
