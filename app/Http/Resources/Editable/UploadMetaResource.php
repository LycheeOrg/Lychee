<?php

namespace App\Http\Resources\Editable;

use App\Enum\FileStatus;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UploadMetaResource extends Data
{
	public function __construct(
		public string $extension,
		public string $uuidName,
		public FileStatus $stage,
		public int $progress,
	) {
	}
}
