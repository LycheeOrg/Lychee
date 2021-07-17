<?php

namespace App\Actions\Album;

use App\Facades\AccessControl;
use App\Models\Album;

class CreateTag extends Action
{
	/**
	 * Create a new smart album based on tags.
	 *
	 * @param string $title
	 * @param string $show_tags
	 *
	 * @return Album
	 */
	public function create(string $title, string $show_tags): Album
	{
		$album = $this->albumFactory->makeFromTitle($title);

		$album->parent_id = null;
		$album->owner_id = AccessControl::id();

		$album->smart = true;
		$album->showtags = $show_tags;
		if (!$album->save()) {
			throw new \RuntimeException('could not persist album to DB');
		}

		return $album;
	}
}
