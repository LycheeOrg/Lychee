<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Insights;

use App\Enum\AspectRatioGroup;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Items in one aspect-ratio group (Feature 085, FR-085-17).
 */
#[TypeScript()]
class AspectRatioData extends Data
{
	public function __construct(
		public AspectRatioGroup $group,
		public int $count,
	) {
	}
}
