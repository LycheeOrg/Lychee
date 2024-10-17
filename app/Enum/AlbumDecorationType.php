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
	case LAYERS = 'layers';
	case ALBUM = 'album';
	case PHOTO = 'photo';
	case ALL = 'all';
}
