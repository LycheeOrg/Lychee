<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Factories;

use App\Assets\Helpers;
use App\Models\Album;

class AlbumFactory
{
	/**
	 * @var SmartFactory
	 */
	private $smartFactory;

	public function __construct(SmartFactory $smartFactory)
	{
		$this->smartFactory = $smartFactory;
	}

	/**
	 * @param string $albumID
	 *
	 * @return Album|SmartAlbum|TagAlbum
	 */
	public function make(string $albumId)
	{
		if ($this->smartFactory->is_smart($albumId)) {
			return $this->smartFactory->make($albumId);
		}

		$album = Album::find($albumId);
		if ($album->smart) {
			return $album->toTagAlbum();
		}

		return $album;
	}

	public function makeFromTitle(string $title): Album
	{
		$album = new Album();
		$album->id = Helpers::generateID();
		$album->title = $title;
		$album->description = '';

		return $album;
	}
}
