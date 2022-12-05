<?php

namespace App\Models\Extensions;

/**
 * Enum SizeVariantType.
 */
enum SizeVariantType: int
{
	case ORIGINAL = 0;
	case MEDIUM2X = 1;
	case MEDIUM = 2;
	case SMALL2X = 3;
	case SMALL = 4;
	case THUMB2X = 5;
	case THUMB = 6;

	/**
	 * Given a sizeVariantType return the associated name.
	 *
	 * @return string
	 */
	public function name(): string
	{
		return match ($this) {
			self::THUMB => 'thumb',
			self::THUMB2X => 'thumb2x',
			self::SMALL => 'small',
			self::SMALL2X => 'small2x',
			self::MEDIUM => 'medium',
			self::MEDIUM2X => 'medium2x',
			self::ORIGINAL => 'original',
		};
	}
}