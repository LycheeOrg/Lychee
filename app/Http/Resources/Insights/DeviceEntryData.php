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
 * One device, manufacturer or lens with its counts (Feature 085, FR-085-12).
 *
 * Entries are keyed by (name, category); `name` null means unknown.
 */
#[TypeScript()]
class DeviceEntryData extends Data
{
	public function __construct(
		public ?string $name,
		public DeviceCategory $category,
		public int $all,
		public int $photos,
		public int $videos,
		public int $highlighted,
		public int $located,
		public int $with_people,
	) {
	}
}
