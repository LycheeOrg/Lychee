<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum LandingLinkPlacement.
 *
 * Defines where an admin-managed landing page extra link is rendered.
 */
enum LandingLinkPlacement: string
{
	case NAV = 'nav';
	case FOOTER = 'footer';
	case BOTH = 'both';
}
