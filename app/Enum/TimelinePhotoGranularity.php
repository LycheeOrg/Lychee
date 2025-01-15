<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * Defines the possible granularities for photo timelines.
 */
enum TimelinePhotoGranularity: string
{
	case DEFAULT = 'default';
	case DISABLED = 'disabled';
	case YEAR = 'year';
	case MONTH = 'month';
	case DAY = 'day';
	case HOUR = 'hour';
}
