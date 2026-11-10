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
 * Image-format section of Insights (Feature 085, FR-085-17).
 */
#[TypeScript()]
class FormatsData extends Data
{
	/**
	 * @param AspectRatioData[] $aspect_ratios
	 */
	public function __construct(
		public OrientationCountData $all,
		public OrientationCountData $photos,
		public OrientationCountData $videos,
		public array $aspect_ratios,
		public DimensionsData $dimensions,
	) {
	}
}
