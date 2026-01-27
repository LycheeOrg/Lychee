<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum DefaultAlbumProtectionType.
 */
enum DefaultAlbumProtectionType: string
{
	case PRIVATE = 'private';
	case PUBLIC = 'public';
	case INHERIT = 'inherit';
	case PUBLIC_HIDDEN = 'public_hidden';
}
