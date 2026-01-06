<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum PhotoLayoutType.
 *
 * All the allowed layout possibilities on Album
 */
enum PhotoLayoutType: string
{
	case SQUARE = 'square';
	case JUSTIFIED = 'justified';
	case MASONRY = 'masonry';
	case GRID = 'grid';
}
