<?php

namespace App\SmartAlbums;

class SmartFactory
{
	/**
	 * @var array
	 */
	public static $base_smarts = ['unsorted', 'starred', 'public', 'recent'];

	/**
	 * Factory method.
	 */
	public function make(string $kind): SmartAlbum
	{
		switch ($kind) {
			case 'starred':
				return resolve(StarredAlbum::class);

			case 'public':
				return resolve(PublicAlbum::class);

			case 'recent':
				return resolve(RecentAlbum::class);

			case 'unsorted':
				return resolve(UnsortedAlbum::class);

			case 'tag':
				return resolve(TagAlbum::class);

			default:
				return null;
		}
	}
}
