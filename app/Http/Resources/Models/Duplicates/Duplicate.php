<?php

namespace App\Http\Resources\Models\Duplicates;

use Spatie\LaravelData\Data;

class Duplicate extends Data
{
	public function __construct(
		public string $album_id,
		public string $album_title,
		public string $photo_id,
		public string $photo_title,
		public string $checksum,
	) {
	}

	/**
	 * @param object{album_id:string,album_title:string,photo_id:string,photo_title:string,checksum:string} $model
	 *
	 * @return Duplicate
	 */
	public static function fromModel(object $model): Duplicate
	{
		return new Duplicate(
			album_id: $model->album_id,
			album_title: $model->album_title,
			photo_id: $model->photo_id,
			photo_title: $model->photo_title,
			checksum: $model->checksum,
		);
	}
}