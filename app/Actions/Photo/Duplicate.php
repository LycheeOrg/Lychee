<?php

namespace App\Actions\Photo;

use App\Exceptions\ModelDBException;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as BaseCollection;

class Duplicate
{
	/**
	 * Duplicates a set of photos.
	 *
	 * @param EloquentCollection<Photo> $photos the source photos
	 * @param Album|null                $album  the destination album; `null` means root album
	 *
	 * @return BaseCollection<Photo> the duplicates
	 *
	 * @throws ModelDBException
	 */
	public function do(EloquentCollection $photos, ?Album $album): BaseCollection
	{
		$duplicates = new BaseCollection();
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
