<?php

namespace App\Enum;

/**
 * Enum DateOrderingType.
 *
 * Determine which date to present first in a min max situation.
 */
enum DateOrderingType: string
{
	case OLDER_YOUNGER = 'older_younger';
	case YOUNGER_OLDER = 'younger_older';
}
