<?php

namespace App\Actions\Album;

use App\Actions\Albums\Extensions\PublicIds;
use App\Models\Album;

class Prepare
{
	/**
	 * @var Photos
	 */
	public $photos;

	public function __construct(Photos $photos)
	{
		$this->photos = $photos;
	}

	/**
	 * @param Album $album
	 *
	 * @return array
	 */
	public function do(Album $album): array
	{
		if ($album->smart) {
			$publicAlbums = resolve(PublicIds::class)->getPublicAlbumsId();
			$album->setAlbumIDs($publicAlbums);
		} else {
			// we only do this when not in smart mode (i.e. no sub albums)
			// that way we limit the number of times we have to query.
			resolve(PublicIds::class)->setAlbum($album);
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
