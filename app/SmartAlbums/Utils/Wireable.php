<?php

namespace App\SmartAlbums\Utils;

use App\Factories\AlbumFactory;
use App\SmartAlbums\BaseSmartAlbum;

trait Wireable
{
	/**
	 * {@inheritdoc}
	 */
	public function toLivewire(): string
	{
		return $this->id;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function fromLivewire(mixed $value): BaseSmartAlbum
	{
		$albumFactory = resolve(AlbumFactory::class);

		return $albumFactory->createSmartAlbum(strval($value), true);
	}
}