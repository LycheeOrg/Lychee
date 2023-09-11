<?php

namespace App\Enum;

/**
 * Enum SizeVariantType.
 *
 * We use int because SizeVariants are stored as int in the database.
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

	/**
	 * Given a sizeVariantType return the localized name.
	 *
	 * @return string
	 */
	public function localized(): string
	{
		return match ($this) {
			self::THUMB => __('lychee.PHOTO_THUMB'),
			self::THUMB2X => __('lychee.PHOTO_THUMB_HIDPI'),
			self::SMALL => __('lychee.PHOTO_SMALL'),
			self::SMALL2X => __('lychee.PHOTO_SMALL_HIDPI'),
			self::MEDIUM => __('lychee.PHOTO_MEDIUM'),
			self::MEDIUM2X => __('lychee.PHOTO_MEDIUM_HIDPI'),
			self::ORIGINAL => __('lychee.PHOTO_ORIGINAL'),
		};
	}
}