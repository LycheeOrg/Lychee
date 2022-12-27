<?php

namespace App\Enum;

/**
 * Enum AlbumDecorationOrientation.
 *
 * All the allowed orientations of Album Decorations
 */
enum AlbumDecorationOrientation: string
{
	case ROW = 'row';
	case ROW_REVERSE = 'row-reverse';
	case COLUMN = 'column';
	case COLUMN_REVERSE = 'column-reverse';
}
