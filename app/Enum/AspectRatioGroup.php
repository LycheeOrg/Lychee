<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Aspect-ratio groups of Insights, independent of orientation
 * (Feature 085, FR-085-17).
 */
enum AspectRatioGroup: string
{
	case SQUARE = '1:1';
	case FIVE_FOUR = '5:4';
	case FOUR_THREE = '4:3';
	case THREE_TWO = '3:2';
	case SIXTEEN_NINE = '16:9';
	case TWO_ONE = '2:1';
	case PANORAMA = 'panorama';
	case OTHER = 'other';

	/**
	 * Long side divided by short side; null for groups without one ratio.
	 */
	public function ratio(): ?float
	{
		return match ($this) {
			self::SQUARE => 1.0,
			self::FIVE_FOUR => 5 / 4,
			self::FOUR_THREE => 4 / 3,
			self::THREE_TWO => 3 / 2,
			self::SIXTEEN_NINE => 16 / 9,
			self::TWO_ONE => 2.0,
			self::PANORAMA, self::OTHER => null,
		};
	}
}
