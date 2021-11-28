<?php

namespace App\Actions\Photo;

use App\Models\Photo;
use Illuminate\Support\Collection;

class Duplicate
{
	/**
	 * Duplicates a set of photos.
	 *
	 * If the ID of the destination album is not given, each photo is
	 * duplicated within its current album.
	 * Note, this implies that photos may be duplicated within different
	 * albums, if the original photos reside in different albums.
	 * If the ID of a destination album is given, then all duplicates are
	 * created in the destination album (this resembles a "copy-to" semantic).
	 *
	 * @param array    $photoIds the IDs of the source photos
	 * @param int|null $albumID  the optional ID of the destination album
	 *
	 * @return Collection the duplicates
	 */
	public function do(array $photoIds, ?int $albumID): Collection
	{
		$duplicates = new Collection();
		$photos = Photo::query()
			->with(['size_variants'])
			->whereIn('id', $photoIds)->get();

		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$duplicate = $photo->replicate();
			if ($albumID !== null) {
				$dstAlbumID = $albumID !== 0 ? $albumID : null;
			} else {
				$dstAlbumID = $photo->album_id;
			}
			$duplicate->album_id = $dstAlbumID;
			$duplicate->save();
			$duplicates->add($duplicate);
		}

		return $duplicates;
	}
}
