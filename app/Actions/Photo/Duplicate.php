<?php

namespace App\Actions\Photo;

use App\Models\Photo;

class Duplicate
{
	public function do(array $photoIds, ?int $albumID)
	{
		$photos = Photo::query()->whereIn('id', $photoIds)->get();

		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$photo->load('size_variants');
			$duplicate = $photo->replicate();
			$duplicate->album_id = ($albumID === 0) ? null : $photo->album_id;
			$duplicate->save();
		}
	}
}
