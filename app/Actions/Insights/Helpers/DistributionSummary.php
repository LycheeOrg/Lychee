<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Helpers;

/**
 * Distinct values of a distribution with their counts and summary figures.
 *
 * `values` is sorted ascending and parallel to `counts`. Summary figures are
 * null when no value was counted.
 */
final readonly class DistributionSummary
{
	/**
	 * @param float[] $values
	 * @param int[]   $counts
	 */
	public function __construct(
		public array $values,
		public array $counts,
		public int $total,
		public int $excluded,
		public float $sum,
		public ?float $min,
		public ?float $max,
		public ?float $median,
		public ?float $mean,
		public ?float $mode,
	) {
	}
}
