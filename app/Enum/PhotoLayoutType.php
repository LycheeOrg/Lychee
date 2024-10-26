<?php

namespace App\Enum;

/**
 * Enum PhotoLayoutType.
 *
 * All the allowed layout possibilities on Album
 */
enum PhotoLayoutType: string
{
	case SQUARE = 'square';
	case JUSTIFIED = 'justified';
	case UNJUSTIFIED = 'unjustified'; // ! Legcay
	case MASONRY = 'masonry';
	case GRID = 'grid';
}
