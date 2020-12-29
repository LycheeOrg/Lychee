<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Actions\Album;

use App\Models\Configs;

class Cast
{
	/**
	 * Set up the wrap arround of the photos if setting is true and if there are enough pictures.
	 */
	public static function wrapAroundPhotos(array &$return_photos): void
	{
		$photo_counter = count($return_photos);

		if ($photo_counter > 1 && Configs::get_value('photos_wraparound', '1') === '1') {
			// Enable next and previous for the first and last photo
			$lastElement = end($return_photos);
			$lastElementId = $lastElement['id'];
			$firstElement = reset($return_photos);
			$firstElementId = $firstElement['id'];

			$return_photos[$photo_counter - 1]['nextPhoto'] = $firstElementId;
			$return_photos[0]['previousPhoto'] = $lastElementId;
		}
	}
}
