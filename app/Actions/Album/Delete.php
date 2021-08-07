<?php

namespace App\Actions\Album;

use App\Contracts\BaseAlbum;
use App\Factories\AlbumFactory;

class Delete
{
	/**
	 * @param array        $albumIDs
	 * @param AlbumFactory $albumFactory
	 *
	 * @return bool
	 */
	public function do(array $albumIDs, AlbumFactory $albumFactory): bool
	{
		$albums = $albumFactory->findWhereIDsIn($albumIDs);
		$success = true;

		/** @var BaseAlbum $album */
		foreach ($albums as $album) {
			$success &= $album->delete();
		}

		return $success;
	}
}
