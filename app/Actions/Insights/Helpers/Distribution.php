<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Helpers;

/**
 * Counts exact values of one EXIF field (Feature 085, FR-085-13).
 *
 * Values are keyed on six decimals so that close shutter fractions such as
 * 1/8000 and 1/10000 stay apart. A `null` value counts as excluded.
 */
final class Distribution
{
	/** @var array<string,int> */
	private array $counts = [];

	/** @var array<string,float> */
	private array $values = [];

	private int $excluded = 0;

	public function add(?float $value): void
	{
		if ($value === null) {
			$this->excluded++;

			return;
		}

		$key = sprintf('%.6F', $value);
		$this->values[$key] = $value;
		$this->counts[$key] = ($this->counts[$key] ?? 0) + 1;
	}

	public function summarise(): DistributionSummary
	{
		$values = $this->values;
		asort($values, SORT_NUMERIC);
		$sorted_values = array_values($values);
		$sorted_counts = array_values(array_map(fn (string $key) => $this->counts[$key], array_keys($values)));

		$total = array_sum($sorted_counts);
		$sum = 0.0;
		foreach ($sorted_values as $i => $value) {
			$sum += $value * $sorted_counts[$i];
		}

		return new DistributionSummary(
			values: $sorted_values,
			counts: $sorted_counts,
			total: $total,
			excluded: $this->excluded,
			sum: $sum,
			min: $sorted_values[0] ?? null,
			max: $sorted_values[count($sorted_values) - 1] ?? null,
			median: self::median($sorted_values, $sorted_counts, $total),
			mean: $total > 0 ? $sum / $total : null,
			mode: self::mode($sorted_values, $sorted_counts),
		);
	}

	/**
	 * @param float[] $values sorted ascending
	 * @param int[]   $counts parallel to $values
	 */
	private static function median(array $values, array $counts, int $total): ?float
	{
		if ($total === 0) {
			return null;
		}

		$lower = self::valueAt($values, $counts, intdiv($total + 1, 2));
		$upper = self::valueAt($values, $counts, intdiv($total, 2) + 1);

		return ($lower + $upper) / 2;
	}

	/**
	 * Value at a 1-based position of the expanded, sorted list.
	 *
	 * @param float[] $values
	 * @param int[]   $counts
	 */
	private static function valueAt(array $values, array $counts, int $position): float
	{
		$seen = 0;
		foreach ($values as $i => $value) {
			$seen += $counts[$i];
			if ($seen >= $position) {
				return $value;
			}
		}

		return $values[count($values) - 1];
	}

	/**
	 * Most frequent value; the lowest one on a tie.
	 *
	 * @param float[] $values sorted ascending
	 * @param int[]   $counts parallel to $values
	 */
	private static function mode(array $values, array $counts): ?float
	{
		$best = null;
		$best_count = 0;
		foreach ($values as $i => $value) {
			if ($counts[$i] > $best_count) {
				$best = $value;
				$best_count = $counts[$i];
			}
		}

		return $best;
	}
}
