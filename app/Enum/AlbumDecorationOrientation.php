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

	/**
	 * Convert into Album Decoration Orientation.
	 *
	 * @return AlbumDecorationOrientation
	 */
	public function toAlbumDecorationOrientation(): AlbumDecorationOrientation
	{
		return AlbumDecorationOrientation::from($this->value);
	}
}
