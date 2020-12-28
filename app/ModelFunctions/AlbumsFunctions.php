<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions;

use AccessControl;
use App\Actions\Albums\PublicIds;
use App\Actions\ReadAccessFunctions;
use App\Models\Album;
use Illuminate\Support\Collection as BaseCollection;

class AlbumsFunctions
{
	use PublicIds;

	/**
	 * @var ReadAccessFunctions
	 */
	private $readAccessFunctions;

	public function __construct(ReadAccessFunctions $readAccessFunctions)
	{
		$this->readAccessFunctions = $readAccessFunctions;
	}

	/**
	 * ? Only used in AlbumsController
	 * Given a list of albums, generate an array to be returned.
	 *
	 * @param Collection[Album]        $albums
	 * @param Collection[Collection[]] $children
	 *
	 * @return array
	 */
	public function prepare_albums(BaseCollection $albums)
	{
		$return = [];
		foreach ($albums->keys() as $key) {
			/**
			 * @var Album
			 */
			$album = $albums[$key];
			$album_array = $album->toArray();

			if (AccessControl::is_logged_in()) {
				$album_array['owner'] = $albums[$key]->owner->name();
			}

			if ($this->readAccessFunctions->album($albums[$key]) === 1) {
				$thumbs = $albums[$key]->get_thumbs();
				$albums[$key]->set_thumbs($album_array, $thumbs);
			}

			// Add to return
			$return[] = $album_array;
		}

		return $return;
	}

	// /**
	//  * @param array[Collection[Album]] $albums_list
	//  *
	//  * @return array
	//  */
	// public function get_children(array $albums_list, $includePassProtected = false)
	// {
	// 	$return = [];
	// 	foreach ($albums_list as $kind => $albums) {
	// 		$return[$kind] = new BaseCollection();

	// 		$albums->each(function ($album, $key) use ($return, $kind, $includePassProtected) {
	// 			$children = new Collection();

	// 			if ($this->readAccessFunctions->album($album) === 1) {
	// 				$children = $this->albumFunctions->get_children($album, 0, $includePassProtected);
	// 			}

	// 			$return[$kind]->put($key, $children);
	// 		});
	// 	}

	// 	return $return;
	// }
}
