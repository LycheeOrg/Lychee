<?php

namespace App\Models\Extensions;

use Illuminate\Support\Collection;

trait AlbumSetters
{
	public function set_thumbs(array &$return, Collection $thumbs = null)
	{
		$return['thumbs'] = [];
		$return['types'] = [];
		$return['thumbs2x'] = [];
		$return['thumbIDs'] = [];

		$thumbs->each(function (Thumb $thumb, $key) use (&$return) {
			$thumb->insertToArrays($return['thumbs'], $return['types'], $return['thumbs2x'], $return['thumbIDs']);
		});
	}
}
