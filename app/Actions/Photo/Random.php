<?php

namespace App\Actions\Photo;

use App\Actions\Albums\Extensions\PublicIds;
use App\Exceptions\JsonError;
use App\SmartAlbums\StarredAlbum;

class Random
{
	public function do(): array
	{
		// here we need to refine.
		$starred = new StarredAlbum();
		$starred->setAlbumIDs(resolve(PublicIds::class)->getPublicAlbumsId());
		$photo = $starred->get_photos()->inRandomOrder()->first();

		if ($photo == null) {
			throw new JsonError('no pictures found!');
		}

		return $photo->toReturnArray();
	}
}
