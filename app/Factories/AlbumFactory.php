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
	 * In the case of is_smart we forward the call to the smart factory.
	 *
	 * @param string|ing
	 */
	public function is_smart($kind): bool
	{
		return $this->smartFactory->is_smart($kind);
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

		//! We need to catch that one, otherwise it is returned as a 404 by Laravel
		$album = Album::findOrFail($albumId);

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
