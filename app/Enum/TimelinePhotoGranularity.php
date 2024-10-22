<?php

namespace App\Enum;

/**
 * Defines the possible granularities for photo timelines.
 */
enum TimelinePhotoGranularity: string
{
	case YEAR = 'year';
	case MONTH = 'month';
	case DAY = 'day';
	case HOUR = 'hour';
}
