<?php

namespace App\Actions\Album;

use App\Models\Album;
use App\Models\Extensions\BaseAlbum;

class Transfer extends Action
{
	/**
	 * Moves the given albums into the target.
	 *
	 * @param BaseAlbum $baseAlbum
	 * @param int       $userId
	 */
	public function do(BaseAlbum $baseAlbum, int $userId): void
	{
		$baseAlbum->owner_id = $userId;
		$baseAlbum->save();

		// If this is an Album, we also need to fix the children and photos ownership
		if ($baseAlbum instanceof Album) {
			$baseAlbum->makeRoot();
			$baseAlbum->save();
			$baseAlbum->fixOwnershipOfChildren();
		}
	}
}
