<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Admin;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ImportFromServerResource extends Data
{
	/**
	 * @param bool                      $status    Overall status of the import operation
	 * @param string                    $message   Result message
	 * @param ImportDirectoryResource[] $results   Collection of directory import results
	 * @param int                       $job_count Total number of jobs dispatched
	 */
	public function __construct(
		public bool $status,
		public string $message,
		#[LiteralTypeScriptType('App.Http.Resources.Admin.ImportDirectoryResource[]')]
		public array $results,
		public int $job_count,
	) {
	}
}
