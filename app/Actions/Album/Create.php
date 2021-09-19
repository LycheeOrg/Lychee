<?php

namespace App\Actions\Album;

use App\Exceptions\ModelDBException;
use App\Facades\AccessControl;
use App\Models\Album;

class Create extends Action
{
	/**
	 * @param string $title
	 * @param int    $parent_id
	 *
	 * @return Album
	 *
	 * @throws ModelDBException
	 */
	public function create(string $title, int $parent_id): Album
	{
		$album = new Album();
		$album->title = $title;
		$this->set_parent($album, $parent_id);
		$album->save();

		return $album;
	}

	/**
	 * Setups parent album on album structure.
	 *
	 * @param Album $album
	 * @param int   $parent_id
	 */
	private function set_parent(Album $album, int $parent_id): void
	{
		/** @var Album|null $parent */
		$parent = Album::query()->find($parent_id);

		// we get the parent if it exists.
		if ($parent !== null) {
			$album->parent_id = $parent->id;

			// Admin can add sub-albums to other users' albums.  Make sure that
			// the ownership stays with that user.
			$album->owner_id = $parent->owner_id;
		} else {
			$album->parent_id = null;
			$album->owner_id = AccessControl::id();
		}
	}
}
