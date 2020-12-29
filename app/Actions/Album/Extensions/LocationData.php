<?php

namespace App\Actions\Album\Extensions;

use App\ModelFunctions\SymLinkFunctions;
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
		$symLinkFunctions = resolve(SymLinkFunctions::class);

		$return_photos = [];
		$photo_counter = 0;
		$photos = $photos_sql->select('album_id', 'id', 'latitude', 'longitude', 'small', 'small2x', 'takestamp', 'thumb2x', 'thumbUrl', 'title', 'type', 'url')
			->whereNotNull('latitude')
			->whereNotNull('longitude')
			->with('album')
			->get();

		/*
		* @var Photo
		*/
		foreach ($photos as $photo_model) {
			// Turn data from the database into a front-end friendly format
			// ! Check if this needs prepareLocationData or to_array
			$photo = $photo_model->prepareLocationData();
			$symLinkFunctions->getUrl($photo_model, $photo);

			// Add to return
			$return_photos[$photo_counter] = $photo;

			$photo_counter++;
		}

		return $return_photos;
	}
}
