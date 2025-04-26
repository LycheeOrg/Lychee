<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Statistics;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class DayCount extends Data
{
	/**
	 * @param string $date  of the count
	 * @param int    $count number
	 *
	 * @return void
	 */
	public function __construct(
		public string $date,
		public int $count,
	) {
	}
}
