<?php

namespace App\Enum;

/**
 * Defines the possible granularities for album timelines.
 */
enum TimelineAlbumGranularity: string
{
	case DEFAULT = 'default';
	case DISABLED = 'disabled';
	case YEAR = 'year';
	case MONTH = 'month';
	case DAY = 'day';
}
