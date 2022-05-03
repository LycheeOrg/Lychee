<?php

namespace App\SmartAlbums\Utils;

use App\Factories\AlbumFactory;

trait Wireable
{
	/**
	 * {@inheritdoc}
	 */
	public function toLivewire()
	{
		return $this->id;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function fromLivewire($value)
	{
		$albumFactory = resolve(AlbumFactory::class);

		return $albumFactory->createSmartAlbum($value, true);
	}
}