<?php

namespace App\Actions\Albums;

use AccessControl;
use App\Actions\ReadAccessFunctions;
use Illuminate\Support\Collection as BaseCollection;

class Prepare
{
	/**
	 * @var ReadAccessFunctions
	 */
	private $readAccessFunctions;

	public function __construct(ReadAccessFunctions $readAccessFunctions)
	{
		$this->readAccessFunctions = $readAccessFunctions;
	}

	/**
	 * Given a list of albums, generate an array to be returned.
	 *
	 * @param Collection[Album] $albums
	 *
	 * @return array
	 */
	public function do(BaseCollection $albums)
	{
		$return = [];
		foreach ($albums->keys() as $key) {
			/**
			 * @var Album
			 */
			$album = $albums[$key];
			$album_array = $album->toReturnArray();

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
}
