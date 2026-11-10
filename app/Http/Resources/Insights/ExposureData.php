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
 * Exposure section of Insights (Feature 085, FR-085-13).
 *
 * Shutter values are seconds, video lengths whole seconds.
 * `total_video_duration` is in seconds.
 */
#[TypeScript()]
class ExposureData extends Data
{
	public function __construct(
		public DistributionData $iso,
		public DistributionData $focal,
		public DistributionData $shutter,
		public DistributionData $aperture,
		public DistributionData $video_length,
		public float $total_video_duration,
	) {
	}
}
