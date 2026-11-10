<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum FrameBoxPosition.
 *
 * Screen position of a Frame overlay box (Feature 088). Positions are
 * physical: TOP_LEFT stays on the left in right-to-left languages.
 * The info box never takes CENTER: its setting lists the four corners only.
 */
enum FrameBoxPosition: string
{
	case TOP_LEFT = 'top_left';
	case TOP_RIGHT = 'top_right';
	case BOTTOM_LEFT = 'bottom_left';
	case BOTTOM_RIGHT = 'bottom_right';
	case CENTER = 'center';
}
