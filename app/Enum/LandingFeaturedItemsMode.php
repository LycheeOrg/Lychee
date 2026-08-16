<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum LandingFeaturedItemsMode.
 *
 * Defines how the landing page featured-content section is populated.
 */
enum LandingFeaturedItemsMode: string
{
	case AUTOMATIC = 'automatic';
	case MANUAL = 'manual';
}
