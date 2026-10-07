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
 * Calendar heatmap of Insights (Feature 085, FR-085-10).
 *
 * Photos per local day: `dates` (`Y-m-d`, ascending) parallel to `counts`,
 * non-empty days only; the client sums weeks. `low`, `medium`, `high` are
 * the per-day colour thresholds from the configuration.
 */
#[TypeScript()]
class CalendarData extends Data
{
	/**
	 * @param string[] $dates
	 * @param int[]    $counts
	 */
	public function __construct(
		public array $dates,
		public array $counts,
		public int $low = 0,
		public int $medium = 0,
		public int $high = 0,
	) {
	}
}
