<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\Statistics;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PhotoStatisticsResource extends Data
{
	public function __construct(
		public int $visit_count = 0,
		public int $download_count = 0,
		public int $favourite_count = 0,
		public int $shared_count = 0,
	) {
	}

	public static function fromModel(Statistics $stats): PhotoStatisticsResource
	{
		return new self(
			$stats->visit_count,
			$stats->download_count,
			$stats->favourite_count,
			$stats->shared_count,
		);
	}
}
