<?php

namespace App\Actions\Album;

use App\Factories\AlbumFactory;
use App\ModelFunctions\AlbumFunctions;
use App\Models\Album;

class Create
{
	/**
	 * @var AlbumFunctions
	 */
	public $albumFunctions;

	/**
	 * @var AlbumFactory
	 */
	public $albumFactory;

	public function __construct(AlbumFunctions $albumFunctions, AlbumFactory $albumFactory)
	{
		$this->albumFactory = $albumFactory;
	}

	/**
	 * @param string $albumID
	 *
	 * @return Album|SmartAlbum
	 */
	public function find(string $albumId): Album
	{
		return $this->albumFactory->make($albumId);
	}
}
