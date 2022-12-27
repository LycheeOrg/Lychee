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
}
