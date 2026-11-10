<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Period covered by Insights (Feature 085, FR-085-05).
 */
enum InsightsPeriodType: string
{
	case LIBRARY = 'library';
	case YEAR = 'year';
	case RANGE = 'range';
}
