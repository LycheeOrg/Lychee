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
		foreach ($albums as $_ => $album) {
			$album_array = $album->toReturnArray();

			if (AccessControl::is_logged_in()) {
				$album_array['owner'] = $album->owner->name();
			}

			$album->set_thumbs($album_array, $album->get_thumbs());

			// Add to return
			$return[] = $album_array;
		}

		return $return;
	}
}
