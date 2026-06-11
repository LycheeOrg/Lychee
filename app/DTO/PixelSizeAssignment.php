<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

use App\Enum\PurchasableLicenseType;
use Money\Money;

readonly class PixelSizeAssignment
{
	public function __construct(
		public int $pixel_size_id,
		public Money $price,
		public PurchasableLicenseType $license_type = PurchasableLicenseType::PERSONAL,
	) {
	}
}
