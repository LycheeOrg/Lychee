<?php

namespace App\Actions\Album;

use App\Contracts\BaseAlbum;

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

		/** @var BaseAlbum $album */
		foreach ($albums as $album) {
			$success &= $album->delete();
		}

		return $success;
	}
}
