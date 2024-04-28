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
	 * Convert the enum into it's translated format.
	 * Note that it is missing owner.
	 *
	 * @return array<string,string>
	 */
	public static function localized(): array
	{
		return [
			self::ROW->value => __('lychee.ALBUM_DECORATION_ORIENTATION_ROW'),
			self::ROW_REVERSE->value => __('lychee.ALBUM_DECORATION_ORIENTATION_ROW_REVERSE'),
			self::COLUMN->value => __('lychee.ALBUM_DECORATION_ORIENTATION_COLUMN'),
			self::COLUMN_REVERSE->value => __('lychee.ALBUM_DECORATION_ORIENTATION_COLUMN_REVERSE'),
		];
	}
}
