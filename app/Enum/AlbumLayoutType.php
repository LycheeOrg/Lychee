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
	case UNJUSTIFIED = 'unjustified'; // ! Legcay
	case MASONRY = 'masonry';
	case GRID = 'grid';

	/**
	 * Convert the enum into it's translated format.
	 * Note that it is missing owner.
	 *
	 * @return array<string,string>
	 */
	public static function localized(): array
	{
		// yes, the UNJUSTIFIED is dropped.
		return [
			self::SQUARE->value => __('lychee.LAYOUT_SQUARES'),
			self::JUSTIFIED->value => __('lychee.LAYOUT_JUSTIFIED'),
			self::MASONRY->value => __('lychee.LAYOUT_MASONRY'),
			self::GRID->value => __('lychee.LAYOUT_GRID'),
		];
	}

}
