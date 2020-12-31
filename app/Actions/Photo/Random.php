<?php

namespace App\Actions\Photo;

use App\Actions\Albums\Extensions\PublicIds;
use App\Response;
use App\SmartAlbums\StarredAlbum;

class Random extends SymLinker
{
	public function do(): array
	{
		// here we need to refine.
		$starred = new StarredAlbum();
		$starred->setAlbumIDs(resolve(PublicIds::class)->getPublicAlbumsId());
		$photo = $starred->get_photos()->inRandomOrder()->first();

		if ($photo == null) {
			return Response::error('no pictures found!');
		}

		$return = $photo->toReturnArray();
		$photo->urls($return);
		$this->symLinkFunctions->getUrl($photo, $return);
		if ($photo->album_id !== null && !$photo->album->is_full_photo_visible()) {
			$photo->downgrade($return);
		}

		return $return;
	}
}
