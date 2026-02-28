<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum DownloadVariantType.
 *
 * Because there are Live photos, it is not possible to merge those types with SizeVariantType
 * This essencially accomplish the sames and provides a type mapping to ensure correct conversions.
 */
enum DownloadVariantType: string
{
	case RAW = 'RAW';
	case LIVEPHOTOVIDEO = 'LIVEPHOTOVIDEO';
	case ORIGINAL = 'ORIGINAL';
	case MEDIUM2X = 'MEDIUM2X';
	case MEDIUM = 'MEDIUM';
	case SMALL2X = 'SMALL2X';
	case SMALL = 'SMALL';
	case THUMB2X = 'THUMB2X';
	case THUMB = 'THUMB';

	/**
	 * Given a DownloadVariantType return the associated SizeVariantType.
	 *
	 * @return SizeVariantType|null
	 */
	public function getSizeVariantType(): SizeVariantType|null
	{
		return match ($this) {
			self::RAW => SizeVariantType::RAW,
			self::THUMB => SizeVariantType::THUMB,
			self::THUMB2X => SizeVariantType::THUMB2X,
			self::SMALL => SizeVariantType::SMALL,
			self::SMALL2X => SizeVariantType::SMALL2X,
			self::MEDIUM => SizeVariantType::MEDIUM,
			self::MEDIUM2X => SizeVariantType::MEDIUM2X,
			self::ORIGINAL => SizeVariantType::ORIGINAL,
			self::LIVEPHOTOVIDEO => null,
		};
	}
}