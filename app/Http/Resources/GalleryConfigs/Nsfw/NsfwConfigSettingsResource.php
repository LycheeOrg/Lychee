<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs\Nsfw;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class NsfwConfigSettingsResource extends Data
{
	public function __construct(
		public string $confidence_threshold,
		public string $area_ratio_threshold,
		public string $debug_detect_threshold,
		public NsfwActionCategoryResource $block,
		public NsfwActionCategoryResource $review,
		public NsfwActionCategoryResource $sensitive,
		public string $queue_backend,
		public string $queue_max_size,
		public string $thread_pool_size,
		public string $verify_ssl,
		public string $workers,
	) {
	}
}
