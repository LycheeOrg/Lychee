<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Actions\Album;

use App\ModelFunctions\PhotoActions\Cast as PhotoCast;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Album;
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

	/**
	 * Given an Album, return the thumbs of its 3 first pictures (excluding subalbums).
	 */
	public static function getThumbs(array &$return, Album $album, SymLinkFunctions $symLinkFunctions): void
	{
		$photos = $album->get_photos()->get();
		$return['thumbs'] = [];
		$return['thumbs2x'] = [];
		$return['types'] = [];
		$return['num'] = strval($photos->count());

		$k = 0;
		foreach ($photos as $photo) {
			if ($k < 3) {
				$ret = PhotoCast::toThumb($photo, $symLinkFunctions);
				$ret->insertToArrays($return['thumbs'], $return['types'], $return['thumbs2x']);
				$k++;
			} else {
				break;
			}
		}
	}
}
