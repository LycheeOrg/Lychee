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
 * Response body of `GET /api/v3/Insights` (Feature 085, API-085-01).
 *
 * Aggregates over one scope (owner or whole instance) and one period, in the
 * local capture time of each photo. `years` lists the local years that have
 * photos in the scope, whatever the period.
 */
#[TypeScript()]
class InsightsResource extends Data
{
	/**
	 * @param int[]               $years
	 * @param TimelineEventData[] $timeline
	 */
	public function __construct(
		public array $years,
		public OverviewData $overview,
		public StorageData $storage,
		public PeopleData $people,
		public PlacesData $places,
		public TimeSpanData $time_span,
		public CalendarData $calendar,
		public RhythmData $rhythm,
		public DevicesData $devices,
		public ExposureData $exposure,
		public FormatsData $formats,
		public array $timeline,
	) {
	}
}
