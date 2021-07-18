<?php

namespace App\Actions\Album\Extensions;

use Illuminate\Database\Eloquent\Builder;

trait LocationData
{
	/**
	 * take a $photo_sql query and return an array containing their pictures.
	 *
	 * @param Builder $photos_sql
	 * @param bool    $full_photo
	 *
	 * @return array
	 */
	public function photosLocationData(Builder $photos_sql)
	{
		$return_photos = [];
		$photo_counter = 0;
		$photos = $photos_sql
			->whereNotNull('latitude')
			->whereNotNull('longitude')
			->with(['album', 'size_variants_raw'])
			->get();

		/*
		* @var Photo
		*/
		foreach ($photos as $photo_model) {
			$return_photos[$photo_counter] = $photo_model->toReturnArray();
			$photo_counter++;
		}

		return $return_photos;
	}
}
