<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Editable;

use App\Enum\FileStatus;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UploadMetaResource extends Data
{
	public function __construct(
		public string $file_name,
		public ?string $extension,
		public ?string $uuid_name,
		public FileStatus $stage,
		public int $chunk_number,
		public int $total_chunks,
	) {
	}
}
