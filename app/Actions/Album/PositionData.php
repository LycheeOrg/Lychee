<?php

namespace App\Actions\Album;

use App\Actions\Album\Extensions\LocationData;
use App\Actions\Albums\Extensions\PublicIds;

class PositionData extends Action
{
	use PublicIds;
	use LocationData;

	public function get(string $albumID, array $data)
	{
		$album = $this->albumFactory->make($albumID);

		if ($album->smart) {
			$album->setAlbumIDs($this->getPublicAlbumsId());
			$photos_sql = $album->get_photos();
		} elseif ($data['includeSubAlbums']) {
			$photos_sql = $album->get_all_photos();
		} else {
			$photos_sql = $album->get_photos();
		}

		$return['photos'] = $this->photosLocationData($photos_sql);
		$return['id'] = strval($album->id);

		return $return;
	}
}
