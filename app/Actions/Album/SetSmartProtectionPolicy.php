<?php

namespace App\Actions\Album;

use App\SmartAlbums\BaseSmartAlbum;

/**
 * Class SetSmartProtectionPolicy.
 */
class SetSmartProtectionPolicy extends Action
{
	/**
	 * @param BaseSmartAlbum $album
	 * @param bool           $isPublic
	 *
	 * @return void
	 */
	public function do(BaseSmartAlbum $album, bool $isPublic): void
	{
		if ($isPublic) {
			$album->setPublic();
		} else {
			$album->setPrivate();
		}
	}
}
