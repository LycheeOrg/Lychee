<?php

namespace App\Actions\Album;

use App\Contracts\AbstractAlbum;
use App\Exceptions\Internal\InvalidSmartIdException;
use App\Exceptions\ModelDBException;
use App\Models\Extensions\BaseAlbum;
use App\SmartAlbums\UnsortedAlbum;

class Delete extends Action
{
	/**
	 * @param string[] $albumIDs
	 *
	 * @throws ModelDBException
	 * @throws InvalidSmartIdException
	 */
	public function do(array $albumIDs): void
	{
		$albums = $this->albumFactory->findWhereIDsIn($albumIDs);
		/** @var AbstractAlbum $album */
		foreach ($albums as $album) {
			if ($album instanceof BaseAlbum || $album instanceof UnsortedAlbum) {
				$album->delete();
			}
		}
	}
}
