<?php

namespace App\Actions\Photo;

use App\Models\Photo;
use Illuminate\Support\Collection;

class Duplicate
{
	public function do(array $photoIds, ?int $albumID): Collection
	{
		$duplicates = new Collection();
		$photos = Photo::query()->whereIn('id', $photoIds)->get();

		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$duplicate = $photo->replicate();
			$duplicate->album_id = ($albumID === 0) ? null : $photo->album_id;
			$duplicate->save();
			$duplicates->add($duplicate);
		}

		return $duplicates;
	}
}
