<?php

namespace App\Actions\Album;

use App\Actions\Album\Cast as AlbumCast;
use App\ModelFunctions\AlbumFunctions;
use App\Models\Album;
use App\SmartAlbums\SmartFactory;

class Get
{
	/**
	 * @var AlbumFunctions
	 */
	public $albumFunctions;

	/**
	 * @var SmartFactory
	 */
	public $smartFactory;

	public function __construct(AlbumFunctions $albumFunctions, SmartFactory $smartFactory)
	{
		$this->albumFunctions = $albumFunctions;
		$this->smartFactory = $smartFactory;
	}

	/**
	 * @param string $albumID
	 *
	 * @return Album|SmartAlbum
	 */
	public function find(string $albumId): Album
	{
		if ($this->albumFunctions->is_smart_album($albumId)) {
			return $this->smartFactory->make($albumId);
		} else {
			$album = Album::find($albumId);
			if ($album->smart) {
				return AlbumCast::toTagAlbum($album);
			} else {
				return $album;
			}
		}
	}
}
