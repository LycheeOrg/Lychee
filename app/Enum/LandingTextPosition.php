<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum LandingTextPosition.
 *
 * Defines the placement of the landing page hero headline/subtitle/CTA.
 * Deliberately not a reuse of AlbumTitlePosition: albums and the landing
 * page are different bounded contexts.
 */
enum LandingTextPosition: string
{
	case TOP_LEFT = 'top_left';
	case TOP_RIGHT = 'top_right';
	case BOTTOM_LEFT = 'bottom_left';
	case BOTTOM_RIGHT = 'bottom_right';
	case CENTER = 'center';
}
