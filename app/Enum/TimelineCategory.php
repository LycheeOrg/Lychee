<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Category of an Insights timeline event (Feature 085, FR-085-19).
 */
enum TimelineCategory: string
{
	case FIRST_LAST = 'first_last';
	case DEVICE = 'device';
	case MILESTONE = 'milestone';
	case RECORD = 'record';
	case BREAK = 'break';
}
