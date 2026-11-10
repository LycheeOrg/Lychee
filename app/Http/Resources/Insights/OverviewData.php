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
 * Overview cards of Insights (Feature 085, FR-085-06).
 */
#[TypeScript()]
class OverviewData extends Data
{
	public function __construct(
		public int $total,
		public int $photos,
		public int $videos,
		public int $others,
		public int $highlighted,
		public int $albums,
		public int $photos_without_album,
	) {
	}
}
