<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\V3;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Response body of `GET /api/v3/Albums/{album_id}/Photos/urls` (Feature 102,
 * FR-102-05, ADR-102-01). Struct-of-Arrays per ADR-0009: both URL arrays are
 * index-aligned to `ids`.
 *
 * A URL is the direct address of the file (disk URL, or S3 URL); `null`
 * means no size variant row exists, or the file needs Lychee-side protection
 * for this viewer and must be loaded through `/Asset`.
 */
#[TypeScript()]
class PhotoUrlResource extends Data
{
	/**
	 * @param string[]        $ids
	 * @param (string|null)[] $small2x_urls grid tiles: first row of `small2x` → `small` → `thumb`
	 * @param (string|null)[] $thumb_urls   list tiles: `thumb`
	 */
	public function __construct(
		public array $ids,
		public array $small2x_urls,
		public array $thumb_urls,
	) {
	}
}
