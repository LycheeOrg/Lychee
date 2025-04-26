<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Editable;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class EditableConfigResource extends Data
{
	public function __construct(
		public string $key,
		public ?string $value,
	) {
	}
}
