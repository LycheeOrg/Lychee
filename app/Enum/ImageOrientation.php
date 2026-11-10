<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Orientation of an item from its pixel size (Feature 085, FR-085-17).
 */
enum ImageOrientation: string
{
	case PORTRAIT = 'portrait';
	case LANDSCAPE = 'landscape';
	case SQUARE = 'square';
	case UNKNOWN = 'unknown';
}
