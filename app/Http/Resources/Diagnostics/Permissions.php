<?php

namespace App\Http\Resources\Diagnostics;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class Permissions extends Data
{
	public function __construct(
		public string $left,
		public string $right,
	) {
	}
}
