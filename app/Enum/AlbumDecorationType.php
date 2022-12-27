<?php

namespace App\Enum;

/**
 * Enum AlbumDecorationType.
 *
 * All the allowed sorting possibilities on Album
 */
enum AlbumDecorationType: string
{
	case NONE = 'none';
	case ORIGINAL = 'original';
	case ALBUM = 'album';
	case PHOTO = 'photo';
	case ALL = 'all';

	/**
	 * Convert into Album Decoration type.
	 *
	 * @return AlbumDecorationType
	 */
	public function toAlbumDecorationType(): AlbumDecorationType
	{
		return AlbumDecorationType::from($this->value);
	}
}
