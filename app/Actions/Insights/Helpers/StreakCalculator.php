<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Helpers;

use Safe\DateTimeImmutable;

/**
 * Computes streaks and breaks from local photo days (Feature 085, FR-085-09).
 *
 * Days are counted as whole days since 1970-01-01 and weeks as Monday-based
 * weeks since the Monday 1969-12-29, so no calendar arithmetic is needed.
 * On a tie the earliest run wins.
 */
final class StreakCalculator
{
	private const SECONDS_PER_DAY = 86400;
	// 1970-01-01 is a Thursday: day 0 sits 3 days into its Monday-based week.
	private const THURSDAY_OFFSET = 3;

	/**
	 * @param string[] $dates unique local dates `Y-m-d`, sorted ascending
	 */
	public static function summarise(array $dates): StreakSummary
	{
		if ($dates === []) {
			return new StreakSummary(0, 0, null, null, null);
		}

		$days = array_map(self::dayIndex(...), $dates);
		// Floor division: days before 1970 are negative and intdiv() would round them towards zero.
		$weeks = array_values(array_unique(array_map(fn (int $day) => intval(floor(($day + self::THURSDAY_OFFSET) / 7)), $days)));

		return new StreakSummary(
			days_with_photos: count($days),
			calendar_days: $days[count($days) - 1] - $days[0] + 1,
			longest_daily_streak: self::longestRun($days, self::date(...)),
			longest_weekly_streak: self::longestRun($weeks, self::weekStart(...)),
			longest_break: self::longestBreak($days),
		);
	}

	/**
	 * Longest run of consecutive indices.
	 *
	 * @param int[]                 $indices sorted, unique
	 * @param \Closure(int): string $label   index → `Y-m-d`
	 */
	private static function longestRun(array $indices, \Closure $label): DateSpan
	{
		$best_start = $indices[0];
		$best_length = 1;
		$start = $indices[0];
		for ($i = 1; $i < count($indices); $i++) {
			if ($indices[$i] !== $indices[$i - 1] + 1) {
				$start = $indices[$i];
			}
			$length = $indices[$i] - $start + 1;
			if ($length > $best_length) {
				$best_start = $start;
				$best_length = $length;
			}
		}

		return new DateSpan($best_length, $label($best_start), $label($best_start + $best_length - 1));
	}

	/**
	 * @param int[] $days sorted, unique
	 */
	private static function longestBreak(array $days): ?DateSpan
	{
		$best = null;
		for ($i = 1; $i < count($days); $i++) {
			$gap = $days[$i] - $days[$i - 1] - 1;
			if ($gap > 0 && ($best === null || $gap > $best->length)) {
				$best = new DateSpan($gap, self::date($days[$i - 1]), self::date($days[$i]));
			}
		}

		return $best;
	}

	private static function dayIndex(string $date): int
	{
		return intdiv((new DateTimeImmutable($date . ' 00:00:00', new \DateTimeZone('UTC')))->getTimestamp(), self::SECONDS_PER_DAY);
	}

	private static function date(int $day): string
	{
		return gmdate('Y-m-d', $day * self::SECONDS_PER_DAY);
	}

	private static function weekStart(int $week): string
	{
		return self::date($week * 7 - self::THURSDAY_OFFSET);
	}
}
