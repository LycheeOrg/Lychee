<?php

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