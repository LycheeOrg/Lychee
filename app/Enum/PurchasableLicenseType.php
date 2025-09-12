<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

use App\Enum\Traits\DecorateBackedEnum;

/**
 * Enum PurchasableLicenseType.
 *
 * License types available for purchase.
 */
enum PurchasableLicenseType: string
{
	use DecorateBackedEnum;

	case PERSONAL = 'personal';
	case COMMERCIAL = 'commercial';
	case EXTENDED = 'extended';
}
