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
 * The local date with the most photos, with one of them as thumbnail.
 */
#[TypeScript()]
class BusiestDayData extends Data
{
	public function __construct(
		public string $date,
		public int $count,
		public string $photo_id,
		public ?string $thumb_url = null,
	) {
	}
}
