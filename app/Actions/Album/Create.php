<?php

namespace App\Actions\Album;

use App\Actions\Album\Extensions\StoreAlbum;
use App\Facades\AccessControl;
use App\Models\Album;

class Create extends Action
{
	use StoreAlbum;

	/**
	 * @param string $albumID
	 *
	 * @return Album|SmartAlbum|Response
	 */
	public function create(string $title, int $parent_id): Album
	{
		$album = $this->albumFactory->makeFromTitle($title);

		$this->set_parent($album, $parent_id);

		return $this->store_album($album);
	}

	/**
	 * Setups parent album on album structure.
	 *
	 * @param Album $album
	 * @param int   $parent_id
	 * @param int   $user_id
	 *
	 * @return Album
	 */
	private function set_parent(Album &$album, int $parent_id): void
	{
		$parent = Album::find($parent_id);

		// we get the parent if it exists.
		if ($parent !== null) {
			$album->parent_id = $parent->id;

			// Admin can add subalbums to other users' albums.  Make sure that
			// the ownership stays with that user.
			$album->owner_id = $parent->owner_id;
		} else {
			$album->parent_id = null;
			$album->owner_id = AccessControl::id();
		}
	}
}
