<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

use App\Exceptions\Internal\TimelineGranularityException;

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

	/**
	 * Return whether the smart album is enabled.
	 *
	 * @return string
	 */
	public function format(): string
	{
		return match ($this) {
			self::YEAR => 'Y',
			self::MONTH => 'Y-m',
			self::DAY => 'Y-m-d',
			self::HOUR => 'Y-m-d H',
			self::DEFAULT, self::DISABLED => throw new TimelineGranularityException(),
		};
	}
}
