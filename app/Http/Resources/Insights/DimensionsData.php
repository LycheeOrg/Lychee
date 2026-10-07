<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Insights;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Most frequent pixel dimensions of the originals (Feature 085, FR-085-17).
 *
 * `widths`, `heights` and `counts` are parallel, most frequent first, at most
 * 200 entries. `formats` is the number of distinct dimensions and
 * `with_dimensions` the number of items that have one.
 */
#[TypeScript()]
class DimensionsData extends Data
{
	/**
	 * @param int[] $widths
	 * @param int[] $heights
	 * @param int[] $counts
	 */
	public function __construct(
		public array $widths,
		public array $heights,
		public array $counts,
		public int $formats,
		public int $with_dimensions,
	) {
	}
}
