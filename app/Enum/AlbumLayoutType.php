<?php

namespace App\Enum;

/**
 * Enum AlbumLayoutType.
 *
 * All the allowed layout possibilities on Album
 */
enum AlbumLayoutType: string
{
	case SQUARE = 'square';
	case JUSTIFIED = 'justified';
	case UNJUSTIFIED = 'unjustified';
}
