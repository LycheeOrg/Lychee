<?php

namespace App\Http\Resources\Models\Utils;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UserToken extends Data
{
	public function __construct(
		public string $token,
	) {
	}
}