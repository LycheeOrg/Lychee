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
 * People section of Insights (Feature 085, FR-085-08).
 *
 * `has_faces` is false when no face was ever detected on the scope's photos,
 * in which case the section is hidden.
 */
#[TypeScript()]
class PeopleData extends Data
{
	public function __construct(
		public bool $has_faces,
		public int $photos_with_people,
		public int $people,
		public int $faces,
		public ?float $faces_per_photo,
	) {
	}
}
