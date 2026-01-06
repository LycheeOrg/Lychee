<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum AlbumLayoutType.
 *
 * All the allowed layout possibilities on Album
 */
enum AlbumLayoutType: string
{
	case LIST = 'list';
	case GRID = 'grid';
}
