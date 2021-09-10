<?php

namespace App\Actions\Album;

use App\Contracts\AbstractAlbum;

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
			$success &= $album->delete();
		}

		return $success;
	}
}
