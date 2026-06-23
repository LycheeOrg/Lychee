<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO\Nsfw;

use App\Enum\NsfwDetectionLabel;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class NsfwDetectionItemData extends Data
{
	public function __construct(
		public NsfwDetectionLabel $label,
		public float $confidence,
		public NsfwBboxData $bbox,
		public int $area_pixels = 0,
		public float $area_ratio = 0.0,
	) {
	}

	/**
	 * Generate a unique key for a detection item based on its label and bounding box.
	 *
	 * @return string
	 */
	public function detectionKey(): string
	{
		return implode(':', [
			$this->label->value,
			$this->bbox->x,
			$this->bbox->y,
			$this->bbox->width,
			$this->bbox->height,
		]);
	}
}
