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
 * Items per orientation (Feature 085, FR-085-17).
 */
#[TypeScript()]
class OrientationCountData extends Data
{
	public function __construct(
		public int $portrait = 0,
		public int $landscape = 0,
		public int $square = 0,
		public int $unknown = 0,
	) {
	}
}
