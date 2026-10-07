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
 * A photo marking a point in time (first or last capture).
 *
 * `taken_at` is the local capture time `Y-m-d H:i`. `thumb_url` is resolved
 * per request, outside the cache, because URLs may be signed.
 */
#[TypeScript()]
class CapturePointData extends Data
{
	public function __construct(
		public string $photo_id,
		public string $taken_at,
		public ?string $thumb_url = null,
	) {
	}
}
