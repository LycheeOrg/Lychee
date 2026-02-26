<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class RenamerPreviewResource extends Data
{
	public string $id;
	public string $original;
	public string $new;

	public function __construct(string $id, string $original, string $new)
	{
		$this->id = $id;
		$this->original = $original;
		$this->new = $new;
	}
}
