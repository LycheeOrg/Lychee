<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Insights;

use App\Enum\DeviceCategory;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Focal lengths used with one device (Feature 085, FR-085-18).
 *
 * `values` (mm, ascending) parallel to `counts`; videos are left out.
 */
#[TypeScript()]
class DeviceFocalData extends Data
{
	/**
	 * @param float[] $values
	 * @param int[]   $counts
	 */
	public function __construct(
		public ?string $name,
		public DeviceCategory $category,
		public array $values,
		public array $counts,
	) {
	}
}
