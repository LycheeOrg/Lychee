<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum DefaultAlbumProtectionType.
 */
enum DefaultAlbumProtectionType: int
{
	case PRIVATE = 1;
	case PUBLIC = 2;
	case INHERIT = 3;
}
