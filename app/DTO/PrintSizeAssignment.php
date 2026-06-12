<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

use Money\Money;

readonly class PrintSizeAssignment
{
	public function __construct(
		public int $print_size_id,
		public Money $price,
	) {
	}
}
