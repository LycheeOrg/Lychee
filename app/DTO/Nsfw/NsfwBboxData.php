<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO\Nsfw;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class NsfwBboxData extends Data
{
	public function __construct(
		public int $x,
		public int $y,
		public int $width,
		public int $height,
	) {
	}
}
