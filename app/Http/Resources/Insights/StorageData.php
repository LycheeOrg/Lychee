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
 * Storage of the originals in the period (Feature 085, FR-085-07).
 *
 * Photos stored without a file size are counted in `size_unknown` and left
 * out of the totals and averages.
 */
#[TypeScript()]
class StorageData extends Data
{
	public function __construct(
		public int $total_size,
		public int $size_unknown,
		public ?int $average_photo_size,
		public ?int $average_video_size,
	) {
	}
}
