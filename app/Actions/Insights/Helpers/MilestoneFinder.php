<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Helpers;

/**
 * Days on which the running count of dated items reaches each milestone
 * (Feature 085, FR-085-19).
 */
final class MilestoneFinder
{
	public const THRESHOLDS = [100, 1000, 5000, 10000, 25000, 50000, 100000, 250000, 500000];

	/**
	 * @param array<string,int> $day_counts local date → items, sorted by date
	 * @param int[]             $thresholds ascending
	 *
	 * @return array<int,string> threshold → local date, reached thresholds only
	 */
	public static function dates(array $day_counts, array $thresholds = self::THRESHOLDS): array
	{
		$reached = [];
		$running = 0;
		$next = 0;
		foreach ($day_counts as $date => $count) {
			$running += $count;
			while ($next < count($thresholds) && $running >= $thresholds[$next]) {
				$reached[$thresholds[$next]] = strval($date);
				$next++;
			}
		}

		return $reached;
	}
}
