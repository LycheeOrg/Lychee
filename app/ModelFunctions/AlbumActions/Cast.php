<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions\AlbumActions;

use App\Album;
use App\Assets\Helpers;
use App\Configs;
use App\ModelFunctions\PhotoActions\Cast as PhotoCast;
use App\ModelFunctions\SymLinkFunctions;
use App\Photo;

class Cast
{
	/**
	 * Returns album-attributes into a front-end friendly format. Note that some attributes remain unchanged.
	 *
	 * @return array
	 */
	public static function toArray(Album $album): array
	{
		return [
			'id' => strval($album->id),
			'title' => $album->title,
			'public' => strval($album->public),
			'full_photo' => Helpers::str_of_bool($album->is_full_photo_visible()),
			'visible' => strval($album->visible_hidden),
			'parent_id' => $album->str_parent_id(),
			'description' => strval($album->description),

			'downloadable' => Helpers::str_of_bool($album->is_downloadable()),
			'share_button_visible' => Helpers::str_of_bool($album->is_share_button_visible()),

			// Parse date
			'sysdate' => $album->created_at->format('F Y'),
			'min_takestamp' => $album->str_min_takestamp(),
			'max_takestamp' => $album->str_max_takestamp(),

			// Parse password
			'password' => Helpers::str_of_bool($album->password != ''),
			'license' => $album->get_license(),

			'thumbs' => [],
			'thumbs2x' => [],
			'types' => [],
		];
	}

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
