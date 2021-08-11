<?php

namespace App\Actions\Album;

use App\Factories\AlbumFactory;

class Action
{
	protected AlbumFactory $albumFactory;

	protected function __construct()
	{
		// instead of using DDI we resolve it. That way we can easily extend from action.
		$this->albumFactory = resolve(AlbumFactory::class);
	}
}
