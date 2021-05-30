<?php

namespace App\Actions\Album;

use App\Actions\Album\Extensions\StoreAlbum;
use App\Facades\AccessControl;
use App\Models\Album;

class CreateTag extends Action
{
	use StoreAlbum;

	/**
	 * Create a new smart album based on tags.
	 *
	 * @param string $title
	 * @param string $show_tags
	 *
	 * @return Album|Response
	 */
	public function create(string $title, string $show_tags): Album
	{
		$album = $this->albumFactory->makeFromTitle($title);

		$album->parent_id = null;
		$album->owner_id = AccessControl::id();

		$album->smart = true;
		$album->showtags = $show_tags;

		return $this->store_album($album);
	}
}
