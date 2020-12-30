<?php

namespace App\Actions\Album;

use App\Actions\Albums\Extensions\PublicIds;
use App\Models\Album;

class Prepare
{
	use PublicIds;

	/**
	 * @var Photos
	 */
	public $photos;

	public function __construct(Photos $photos)
	{
		$this->photos = $photos;
	}

	/**
	 * @param string $albumID
	 *
	 * @return array
	 */
	public function do(Album $album): array
	{
		$return = ['albums' => []];

		if ($album->smart) {
			$publicAlbums = $this->getPublicAlbumsId();
			$album->setAlbumIDs($publicAlbums);
		}
		$return = $album->toReturnArray();

		// take care of sub albums
		$return['albums'] = $album->get_children()->map(function ($child) {
			$arr_child = $child->toReturnArray();
			$child->set_thumbs($arr_child, $child->get_thumbs());

			return $arr_child;
		})->values();

		// take care of photos
		$return['photos'] = $this->photos->get($album);
		$return['id'] = $album->id;
		$return['num'] = strval(count($return['photos']));

		// finalize the loop
		if ($return['num'] === '0') {
			$return['photos'] = false;
		}

		return $return;
	}
}
