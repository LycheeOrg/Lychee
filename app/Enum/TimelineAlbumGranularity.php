<?php

namespace App\Enum;

/**
 * Defines the possible granularities for album timelines.
 */
enum TimelineAlbumGranularity: string
{
	case YEAR = 'year';
	case MONTH = 'month';
	case DAY = 'day';
}
