<?php

namespace App\Actions\Photo;

use App\Actions\Albums\Extensions\PublicIds;
use App\Exceptions\JsonError;
use App\Models\Photo;
use App\SmartAlbums\StarredAlbum;

class Random
{
	public function do(): Photo
	{
		// here we need to refine.
		$starred = new StarredAlbum();
		$starred->setAlbumIDs(resolve(PublicIds::class)->getPublicAlbumsId());
		/** @var Photo $photo */
		$photo = $starred->get_photos()->inRandomOrder()->first();

		if ($photo == null) {
			throw new JsonError('no pictures found!');
		}

		return $photo;
	}
}
