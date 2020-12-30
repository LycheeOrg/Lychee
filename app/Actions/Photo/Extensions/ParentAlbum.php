<?php

namespace App\Actions\Photo\Extensions;

use App\Actions\Album\UpdateTakestamps;
use App\Exceptions\JsonError;
use App\Factories\AlbumFactory;
use App\Models\Logs;

trait ParentAlbum
{
	public function updateParentAlbum()
	{
		$updateTakestamps = resolve(UpdateTakestamps::class);

		if ($this->parentAlbum != null) {
			if (!$updateTakestamps->singleAndSave($this->parentAlbum)) {
				Logs::error(__METHOD__, __LINE__, 'Could not update album takestamps');

				throw new JsonError('Could not update album takestamps');
			}
		}
	}

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
