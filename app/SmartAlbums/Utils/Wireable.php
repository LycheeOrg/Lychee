<?php

namespace App\SmartAlbums\Utils;

trait Wireable
{
	/**
	 * {@inheritdoc}
	 */
	public function toLivewire()
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function fromLivewire($value)
	{
		return self::getInstance();
	}
}