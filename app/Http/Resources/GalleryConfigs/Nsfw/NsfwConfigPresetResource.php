<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs\Nsfw;

use App\Enum\NsfwPreset;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class NsfwConfigPresetResource extends Data
{
	public function __construct(
		public NsfwPreset $name,
		public string $description,
		public NsfwActionCategoryResource $block,
		public NsfwActionCategoryResource $review,
		public NsfwActionCategoryResource $sensitive,
	) {
	}
}
