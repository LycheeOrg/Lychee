<?php

namespace App\Actions\Album\Extensions;

use App\Models\Photo;
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
			->with(['album', 'size_variants_raw', 'size_variants_raw.sym_links'])
			->get();

		/** @var Photo $photo_model */
		foreach ($photos as $photo_model) {
			$return_photos[$photo_counter] = $photo_model->toArray();
			$photo_counter++;
		}

		return $return_photos;
	}
}
