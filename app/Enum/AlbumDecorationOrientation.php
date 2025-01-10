<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum AlbumDecorationOrientation.
 *
 * All the allowed orientations of Album Decorations
 */
enum AlbumDecorationOrientation: string
{
	case ROW = 'row';
	case ROW_REVERSE = 'row-reverse';
	case COLUMN = 'column';
	case COLUMN_REVERSE = 'column-reverse';
}
