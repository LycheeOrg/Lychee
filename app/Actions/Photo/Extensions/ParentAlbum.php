<?php

namespace App\Actions\Photo\Extensions;

use App\Exceptions\JsonError;
use App\Factories\AlbumFactory;

trait ParentAlbum
{
	public function initParentId($albumID_in)
	{
		/** @var AlbumFactory */
		$factory = resolve(AlbumFactory::class);

		/* @var Album */
		$this->albumID = null;
		if ($albumID_in != '0') {
			$album = $factory->make($albumID_in);

			if ($album->is_tag_album()) {
				throw new JsonError('Sorry, cannot upload to Tag Album.');
			}

			if (!$album->is_smart()) {
				$this->parentAlbum = $album; // we save it so we don't have to query it again later
				$this->albumID = $albumID_in;
			} else {
				$this->public = ($album->id == 'public');
				$this->star = ($album->id == 'starred');
			}
		}
	}
}
