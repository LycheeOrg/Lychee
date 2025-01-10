<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum ThumbOverlayVisibilityType.
 *
 * All the allowed display possibilities of the overlay on thumbs
 */
enum ThumbOverlayVisibilityType: string
{
	case NEVER = 'never';
	case ALWAYS = 'always';
	case HOVER = 'hover';
}
