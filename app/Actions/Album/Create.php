<?php

namespace App\Actions\Album;

use App\Facades\AccessControl;
use App\Models\Album;

class Create extends Action
{
	/**
	 * @param string      $title
	 * @param string|null $parent_id
	 *
	 * @return Album
	 */
	public function create(string $title, ?string $parent_id = null): Album
	{
		$album = new Album();
		$album->title = $title;
		$this->set_parent($album, $parent_id);
		if (!$album->save()) {
			throw new \RuntimeException('could not persist album to DB');
		}

		return $album;
	}

	/**
	 * Setups parent album on album structure.
	 *
	 * @param Album       $album
	 * @param string|null $parent_id
	 */
	private function set_parent(Album $album, ?string $parent_id): void
	{
		if ($parent_id !== null) {
			/** @var Album $parent */
			$parent = Album::query()->findOrFail($parent_id);
			$album->parent_id = $parent_id;

			// Admin can add subalbums to other users' albums.  Make sure that
			// the ownership stays with that user.
			$album->owner_id = $parent->owner_id;
		} else {
			$album->parent_id = null;
			$album->owner_id = AccessControl::id();
		}
	}
}
