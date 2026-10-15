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
 * Response body of `GET /api/v3/People` (Feature 093, API-093-04).
 *
 * Struct-of-Arrays per ADR-0009, ordered by name. Whole-scope and
 * unpaginated (ADR-069-01, strategy 1): every person the caller may pick.
 * Names are not unique; `crop_urls[i]` (the representative face) and
 * `face_counts[i]` let the client tell homonyms apart.
 */
#[TypeScript()]
class PeopleListResource extends Data
{
	/**
	 * @param string[]        $ids
	 * @param string[]        $names
	 * @param int[]           $face_counts
	 * @param (string|null)[] $crop_urls
	 */
	public function __construct(
		public array $ids,
		public array $names,
		public array $face_counts,
		public array $crop_urls,
	) {
	}
}
