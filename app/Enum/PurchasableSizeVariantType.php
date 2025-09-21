<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

use App\Enum\Traits\DecorateBackedEnum;

/**
 * Enum SizeVariantType.
 *
 * Types of size variants available for purchase.
 *
 * This is a subset of SizeVariantType as not all sizes are purchasable.
 * This also adds an extra "size": FULL.
 *
 * The difference between FULL and ORIGINAL is that ORIGINAL is the largest
 * size that is uploaded on lychee, while FULL is the largest size that can be exported by the photographer.
 *
 * FULL will require an extra export from the photographer to complete the purchase while original can be directly downloaded.
 */
enum PurchasableSizeVariantType: string
{
	use DecorateBackedEnum;

	case MEDIUM = 'medium';
	case MEDIUM2x = 'medium2x';
	case ORIGINAL = 'original';
	case FULL = 'full';
}
