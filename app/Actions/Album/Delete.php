<?php

namespace App\Actions\Album;

use App\Contracts\AbstractAlbum;
use App\Exceptions\ModelDBException;

class Delete extends Action
{
	/**
	 * @param array $albumIDs
	 *
	 * @throws ModelDBException
	 */
	public function do(array $albumIDs): void
	{
		$albums = $this->albumFactory->findWhereIDsIn($albumIDs);
		$success = true;
		/** @var AbstractAlbum $album */
		foreach ($albums as $album) {
			$success &= $album->delete();
		}
		if (!$success) {
			throw ModelDBException::create('albums', 'delete');
		}
	}
}
