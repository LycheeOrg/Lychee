<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs\Nsfw;

use App\Enum\NsfwDetectionLabel;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class NsfwActionCategoryResource extends Data
{
	/**
	 * @param NsfwDetectionLabel[] $labels
	 * @param array<string,float>  $label_thresholds
	 */
	public function __construct(
		#[LiteralTypeScriptType('App.Enum.NsfwDetectionLabel[]')]
		public array $labels,
		public ?float $confidence,
		public ?float $area_ratio,
		#[LiteralTypeScriptType('number[]')]
		public array $label_thresholds = [],
	) {
	}
}
