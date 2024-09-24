<?php

namespace App\Http\Resources\Frame;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class FrameData extends Data
{
	public function __construct(
		public int $timeout,
		public string $src,
		public string $srcset,
	) {
	}
}
