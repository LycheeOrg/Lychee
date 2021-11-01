<?php

namespace App\Actions\Album;

use App\Contracts\AbstractAlbum;
use App\Models\Extensions\BaseAlbum;
use App\SmartAlbums\UnsortedAlbum;

class Delete extends Action
{
	/**
	 * @param array $albumIDs
	 *
	 * @return bool
	 */
	public function do(array $albumIDs): bool
	{
		$albums = $this->albumFactory->findWhereIDsIn($albumIDs);
		$success = true;

		/** @var AbstractAlbum $album */
		foreach ($albums as $album) {
			if ($album instanceof BaseAlbum || $album instanceof UnsortedAlbum) {
				$success &= $album->delete();
			}
		}

		return $success;
	}
}
