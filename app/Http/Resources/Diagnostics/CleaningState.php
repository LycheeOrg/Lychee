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
class CleaningState extends Data
{
	public string $path;
	public string $base;
	public bool $is_not_empty;

	public function __construct(
		string $path,
		bool $is_not_empty,
	) {
		$this->path = str_replace(storage_path() . '/', '', $path);
		$this->base = str_replace(base_path() . '/', '', $path);
		$this->is_not_empty = $is_not_empty;
	}
}
