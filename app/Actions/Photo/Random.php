<?php

namespace App\Actions\Photo;

use App\Exceptions\JsonError;
use App\Models\Photo;
use App\SmartAlbums\StarredAlbum;

class Random
{
	public function do(): Photo
	{
		$starred = StarredAlbum::getInstance();
		/** @var Photo $photo */
		$photo = $starred->photos()->inRandomOrder()->first();

		if ($photo == null) {
			throw new JsonError('no pictures found!');
		}

		return $photo;
	}
}
