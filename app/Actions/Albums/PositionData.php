<?php

namespace App\Actions\Albums;

use App\Actions\Album\Extensions\LocationData;
use App\Actions\Albums\Extensions\PublicIds;
use App\Models\Configs;
use App\Models\Photo;

class PositionData
{
	use LocationData;

	/**
	 * Given a list of albums, generate an array to be returned.
	 *
	 * @param Collection<Album> $albums
	 *
	 * @return array
	 */
	public function do()
	{
		// caching to avoid further request
		Configs::get();

		// Initialize return var
		$return = [];

		$albumIDs = resolve(PublicIds::class)->getPublicAlbumsId();

		$query = Photo::with('album')->whereIn('album_id', $albumIDs);

		$full_photo = Configs::get_value('full_photo', '1') == '1';
		$return['photos'] = $this->photosLocationData($query, $full_photo);

		return $return;
	}
}
