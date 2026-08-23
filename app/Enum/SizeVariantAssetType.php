<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum SizeVariantAssetType.
 */
enum SizeVariantAssetType: string
{
	case SMALL2X = 'small2x';
	case SMALL = 'small';
	case THUMB2X = 'thumb2x';
	case THUMB = 'thumb';
	case PLACEHOLDER = 'placeholder';

	/**
	 * Given a SizeVariantAssetType return the associated SizeVariantType.
	 *
	 * @return SizeVariantType
	 */
	public function toSizeVariantType(): SizeVariantType
	{
		return match ($this) {
			self::SMALL2X => SizeVariantType::SMALL2X,
			self::SMALL => SizeVariantType::SMALL,
			self::THUMB2X => SizeVariantType::THUMB2X,
			self::THUMB => SizeVariantType::THUMB,
			self::PLACEHOLDER => SizeVariantType::PLACEHOLDER,
		};
	}
}