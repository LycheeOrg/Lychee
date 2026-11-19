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
 * Rhythm section of Insights (Feature 085, FR-085-11), in local time.
 *
 * `week_hour[weekday][hour]` with Monday = 0; `months[0]` is January.
 */
#[TypeScript()]
class RhythmData extends Data
{
	/**
	 * @param int[][] $week_hour
	 * @param int[]   $months
	 * @param int[]   $weekdays
	 * @param int[]   $hours
	 */
	public function __construct(
		public array $week_hour,
		public array $months,
		public array $weekdays,
		public array $hours,
	) {
	}
}
