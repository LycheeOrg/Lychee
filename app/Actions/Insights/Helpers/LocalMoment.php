<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Helpers;

/**
 * A capture time read in the timezone the photo was taken in.
 */
final readonly class LocalMoment
{
	/**
	 * @param string $date       local date, `Y-m-d`
	 * @param int    $year       local year
	 * @param int    $month      1 … 12
	 * @param string $week_start Monday of the ISO week, `Y-m-d`
	 * @param int    $weekday    0 (Monday) … 6 (Sunday)
	 * @param int    $hour       0 … 23
	 * @param string $time       local time `H:i`
	 */
	public function __construct(
		public string $date,
		public int $year,
		public int $month,
		public string $week_start,
		public int $weekday,
		public int $hour,
		public string $time,
	) {
	}
}
