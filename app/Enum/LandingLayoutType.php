<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum LandingLayoutType.
 *
 * Defines the selectable landing page layouts.
 */
enum LandingLayoutType: string
{
	case CLASSIC = 'classic';
	case PORTFOLIO = 'portfolio';
	case MERIDIAN = 'meridian';
	case STUDIO = 'studio';
}
