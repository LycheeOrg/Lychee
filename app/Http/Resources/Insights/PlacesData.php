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
 * Places section of Insights (Feature 085, FR-085-14).
 *
 * `share` is the located fraction (0 … 1) of the photos in the period.
 */
#[TypeScript()]
class PlacesData extends Data
{
	public function __construct(
		public int $located,
		public float $share,
	) {
	}
}
