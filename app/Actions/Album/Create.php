<?php

namespace App\Actions\Album;

use App\Exceptions\ModelDBException;
use App\Facades\AccessControl;
use App\Models\Album;

class Create extends Action
{
	/**
	 * @param string     $title
	 * @param Album|null $parentAlbum
	 *
	 * @return Album
	 *
	 * @throws ModelDBException
	 */
	public function create(string $title, ?Album $parentAlbum): Album
	{
		$album = new Album();
		$album->title = $title;
		$this->set_parent($album, $parentAlbum);
		$album->save();

		return $album;
	}

	/**
	 * Setups parent album on album structure.
	 *
	 * @param Album      $album
	 * @param Album|null $parentAlbum
	 */
	private function set_parent(Album $album, ?Album $parentAlbum): void
	{
		if ($parentAlbum !== null) {
			// Admin can add sub-albums to other users' albums.  Make sure that
			// the ownership stays with that user.
			$album->owner_id = $parentAlbum->owner_id;
			// Don't set attribute `parent_id` manually, but use specialized
			// methods of the nested set `NodeTrait`.
			$album->appendToNode($parentAlbum);
		} else {
			$album->owner_id = AccessControl::id();
			$album->makeRoot();
		}
	}
}
