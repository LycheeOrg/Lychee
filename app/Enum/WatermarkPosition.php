<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum representing the possible position for watermarking.
 */
enum WatermarkPosition: string
{
	case TOP_LEFT = 'top-left';
	case TOP = 'top';
	case TOP_RIGHT = 'top-right';

	case LEFT = 'left';
	case CENTER = 'center';
	case RIGHT = 'right';

	case BOTTOM_LEFT = 'bottom-left';
	case BOTTOM = 'bottom';
	case BOTTOM_RIGHT = 'bottom-right';
}
