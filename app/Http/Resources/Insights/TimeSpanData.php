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
 * Time-span section of Insights (Feature 085, FR-085-09).
 *
 * `calendar_days` counts the days from the first to the last photo day.
 */
#[TypeScript()]
class TimeSpanData extends Data
{
	public function __construct(
		public ?CapturePointData $first,
		public ?CapturePointData $last,
		public ?BusiestDayData $busiest_day,
		public int $days_with_photos,
		public int $calendar_days,
		public int $undated,
		public ?SpanData $longest_break,
		public ?SpanData $longest_daily_streak,
		public ?SpanData $longest_weekly_streak,
	) {
	}
}
