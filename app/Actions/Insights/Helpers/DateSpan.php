<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Helpers;

/**
 * A run of days or weeks between two dates (both `Y-m-d`).
 */
final readonly class DateSpan
{
	public function __construct(
		public int $length,
		public string $from,
		public string $to,
	) {
	}
}
