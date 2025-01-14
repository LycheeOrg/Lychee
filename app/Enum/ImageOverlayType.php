<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum ImageOverlayType.
 *
 * All the allowed overlay info on Photos
 */
enum ImageOverlayType: string
{
	case NONE = 'none';
	case DESC = 'desc';
	case DATE = 'date';
	case EXIF = 'exif';
}
