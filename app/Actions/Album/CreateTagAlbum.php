<?php

namespace App\Actions\Album;

use App\Facades\AccessControl;
use App\Models\TagAlbum;

class CreateTagAlbum extends Action
{
	/**
	 * Create a new smart album based on tags.
	 *
	 * @param string $title
	 * @param string $show_tags
	 *
	 * @return TagAlbum
	 */
	public function create(string $title, string $show_tags): TagAlbum
	{
		$album = new TagAlbum();
		$album->title = $title;
		$album->show_tags = $show_tags;
		$album->owner_id = AccessControl::id();
		if (!$album->save()) {
			throw new \RuntimeException('could not persist album to DB');
		}

		return $album;
	}
}
