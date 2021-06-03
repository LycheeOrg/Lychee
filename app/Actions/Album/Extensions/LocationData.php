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
		$photos = $photos_sql
			->whereNotNull('latitude')
			->whereNotNull('longitude')
			->get();

		/*
		* @var Photo
		*/
		foreach ($photos as $photo_model) {
			$photo = $photo_model->toReturnArray();
			$symLinkFunctions->getUrl($photo_model, $photo);

			// Add to return
			$return_photos[$photo_counter] = $photo;

			$photo_counter++;
		}

		return $return_photos;
	}
}
