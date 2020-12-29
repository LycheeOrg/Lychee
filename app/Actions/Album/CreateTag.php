<?php

namespace App\Actions\Album;

use AccessControl;
use App\Actions\Album\Extensions\StoreAlbum;
use App\Factories\AlbumFactory;
use App\Models\Album;

class CreateTag
{
	use StoreAlbum;

	/**
	 * @var AlbumFactory
	 */
	public $albumFactory;

	public function __construct(AlbumFactory $albumFactory)
	{
		$this->albumFactory = $albumFactory;
	}

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
