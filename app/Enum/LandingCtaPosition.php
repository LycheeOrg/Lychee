<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum representing the possible positions for the landing page CTA button.
 *
 * Landing-scoped (deliberately not shared with WatermarkPosition, even
 * though the value set is identical): landing and watermarking are
 * different bounded contexts, see Feature 054 spec Design Notes.
 */
enum LandingCtaPosition: string
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
