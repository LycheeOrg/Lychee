<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use Money\Money;

readonly class PurchasableOption
{
	public function __construct(
		public PurchasableSizeVariantType $size_variant,
		public PurchasableLicenseType $license_type,
		public Money $price,
		public int $purchasable_id,
	) {
	}
}