<?php

namespace App\Models\Extensions;

trait AlbumSetters
{
	public function set_thumb(array &$return, Thumb $thumb = null)
	{
		$return['thumb'] = optional($thumb)->toArray();
	}
}
