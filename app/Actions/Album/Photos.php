<?php

namespace App\Actions\Album;

use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;

class Photos
{
	/**
	 * take a $photo_sql query and return an array containing their pictures.
	 *
	 * @param bool $full_photo
	 *
	 * @return array
	 */
	public function get(Album $album): array
	{
		[$sortingCol, $sortingOrder] = $album->get_sort();
		$photos_sql = $album->get_photos()->with('size_variants_raw');

		$previousPhotoID = '';
		$return_photos = [];
		$photo_counter = 0;

		/**
		 * @var Collection[Photo]
		 */
		$photos = $album->customSort($photos_sql, $sortingCol, $sortingOrder);

		if ($sortingCol === 'title' || $sortingCol === 'description') {
			// The result is supposed to be sorted by the user-specified
			// column as the primary key and by 'id' as the secondary key.
			// Unfortunately, sortBy can't be chained the way orderBy can.
			// Instead, we use array_multisort which can be used in a
			// stable fashion, preserving the ordering of elements that
			// compare equal.  We depend here on the collection already
			// being sorted by 'id', via the SQL query.

			// Convert to array so that we can use standard PHP functions.
			// TODO: use collections?
			// * see if this works
			// $photos = $photos
			// 	->sortBy($sortingCol, SORT_NATURAL | SORT_FLAG_CASE, $sortingOrder === 'ASC' ? SORT_ASC : SORT_DESC)
			// 	->sortBy('id', SORT_ASC);
			$photos = $photos->all();
			// Primary sorting key.
			$values = array_column($photos, $sortingCol);
			// Secondary sorting key -- just preserves current order.
			$keys = array_keys($photos);
			array_multisort($values, $sortingOrder === 'ASC' ? SORT_ASC : SORT_DESC, SORT_NATURAL | SORT_FLAG_CASE, $keys, SORT_ASC, $photos);
		}

		/** @var Photo $photo_model */
		foreach ($photos as $photo_model) {
			// Turn data from the database into a front-end friendly format
			$photo = $photo_model->toReturnArray();

			// Set previous and next photoID for navigation purposes
			$photo['previousPhoto'] = $previousPhotoID;
			$photo['nextPhoto'] = '';

			// Set current photoID as nextPhoto of previous photo
			if ($previousPhotoID !== '') {
				$return_photos[$photo_counter - 1]['nextPhoto'] = $photo['id'];
			}
			$previousPhotoID = $photo['id'];

			// Add to return
			$return_photos[$photo_counter] = $photo;

			$photo_counter++;
		}

		$this->wrapAroundPhotos($return_photos);

		return $return_photos;
	}

	/**
	 * Set up the wrap around of the photos if setting is true and if there are enough pictures.
	 */
	private function wrapAroundPhotos(array &$return_photos): void
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
