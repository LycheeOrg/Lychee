<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Insights;

use App\Actions\Insights\Helpers\DistributionSummary;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Distinct values of one EXIF field with counts and summary figures
 * (Feature 085, FR-085-13). `values` is ascending and parallel to `counts`.
 */
#[TypeScript()]
class DistributionData extends Data
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
		public ?float $min,
		public ?float $max,
		public ?float $median,
		public ?float $mean,
		public ?float $mode,
	) {
	}

	public static function fromSummary(DistributionSummary $summary): self
	{
		return new self(
			values: $summary->values,
			counts: $summary->counts,
			total: $summary->total,
			excluded: $summary->excluded,
			min: $summary->min,
			max: $summary->max,
			median: $summary->median,
			mean: $summary->mean,
			mode: $summary->mode,
		);
	}
}
