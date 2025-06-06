<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\Statistics;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class AlbumStatisticsResource extends Data
{
	public function __construct(
		public int $visit_count = 0,
		public int $download_count = 0,
		public int $shared_count = 0,
	) {
	}

	public static function fromModel(Statistics $stats): AlbumStatisticsResource
	{
		return new self(
			$stats->visit_count,
			$stats->download_count,
			$stats->shared_count,
		);
	}
}
