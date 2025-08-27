<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Admin;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ImportDirectoryResource extends Data
{
	/**
	 * @param string      $directory  The directory path
	 * @param bool        $status     Status of the import operation
	 * @param string|null $message    Optional error message
	 * @param int|null    $jobs_count Number of jobs created for this directory
	 */
	public function __construct(
		public string $directory,
		public bool $status,
		public ?string $message = null,
		public ?int $jobs_count = null,
	) {
	}
}
