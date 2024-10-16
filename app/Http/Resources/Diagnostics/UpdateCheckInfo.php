<?php

namespace App\Http\Resources\Diagnostics;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UpdateCheckInfo extends Data
{
	public function __construct(
		public string $extra,
		public bool $can_update,
	) {
	}
}
