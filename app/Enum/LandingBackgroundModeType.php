<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum LandingBackgroundModeType.
 *
 * Defines allowed modes for landing page background resolution.
 */
enum LandingBackgroundModeType: string
{
	case STATIC = 'static';
	case PHOTO_ID = 'photo_id';
	case RANDOM = 'random';
	case LATEST_ALBUM_COVER = 'latest_album_cover';
	case RANDOM_FROM_ALBUM = 'random_from_album';
}
