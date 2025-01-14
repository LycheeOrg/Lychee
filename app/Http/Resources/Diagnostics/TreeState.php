<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Diagnostics;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class TreeState extends Data
{
	public function __construct(
		public int $oddness,
		public int $duplicates,
		public int $wrong_parent,
		public int $missing_parent,
	) {
	}
}
