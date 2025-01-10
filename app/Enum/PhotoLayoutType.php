<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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
	case UNJUSTIFIED = 'unjustified'; // ! Legcay
	case MASONRY = 'masonry';
	case GRID = 'grid';
}
