<?php

namespace App\Actions\Photo\Extensions;

use App\Models\Photo;

/**
 * Trait Checks.
 *
 * TODO: This trait should be liquidated.
 * It is only used in a single place.
 */
trait Checks
{
	/**
	 * Check if a picture has a duplicate
	 * We compare the checksum to the other Photos or LivePhotos.
	 *
	 * TODO: Move this method to where it belongs.
	 *
	 * @param string $checksum
	 *
	 * @return ?Photo
	 */
	public function get_duplicate(string $checksum): ?Photo
	{
		/** @var Photo|null $photo */
		$photo = Photo::query()
			->where('checksum', '=', $checksum)
			->orWhere('original_checksum', '=', $checksum)
			->orWhere('live_photo_checksum', '=', $checksum)
			->first();

		return $photo;
	}
}
