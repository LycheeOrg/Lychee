<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models\Duplicates;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class DuplicateCount extends Data
{
	public function __construct(
		public int $pure_duplicates,
		public int $title_duplicates,
		public int $duplicates_within_album,
	) {
	}
}