<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

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
	case PLACEHOLDER = 7;

	/**
	 * Given a sizeVariantType return the associated name.
	 *
	 * @return string
	 */
	public function name(): string
	{
		return match ($this) {
			self::PLACEHOLDER => 'placeholder',
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
	public function localization(): string
	{
		return match ($this) {
			self::PLACEHOLDER => __('gallery.placeholder'),
			self::THUMB => __('gallery.thumb'),
			self::THUMB2X => __('gallery.thumb_hidpi'),
			self::SMALL => __('gallery.small'),
			self::SMALL2X => __('gallery.small_hidpi'),
			self::MEDIUM => __('gallery.medium'),
			self::MEDIUM2X => __('gallery.medium_hidpi'),
			self::ORIGINAL => __('gallery.original'),
		};
	}
}