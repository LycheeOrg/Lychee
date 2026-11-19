<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Helpers;

/**
 * Streaks and breaks of a set of photo days.
 *
 * `longest_break` runs between the two photo days around it; its length is
 * the number of days without photos in between.
 */
final readonly class StreakSummary
{
	public function __construct(
		public int $days_with_photos,
		public int $calendar_days,
		public ?DateSpan $longest_daily_streak,
		public ?DateSpan $longest_weekly_streak,
		public ?DateSpan $longest_break,
	) {
	}
}
