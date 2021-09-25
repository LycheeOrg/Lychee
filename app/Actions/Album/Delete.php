<?php

namespace App\Actions\Album;

use App\Contracts\AbstractAlbum;
use App\Exceptions\Internal\InvalidSmartIdException;
use App\Exceptions\ModelDBException;

class Delete extends Action
{
	/**
	 * @param int[] $albumIDs
	 *
	 * @throws ModelDBException
	 * @throws InvalidSmartIdException
	 */
	public function do(array $albumIDs): void
	{
		$albums = $this->albumFactory->findWhereIDsIn($albumIDs);
		/** @var AbstractAlbum $album */
		foreach ($albums as $album) {
			$album->delete();
		}
	}
}
