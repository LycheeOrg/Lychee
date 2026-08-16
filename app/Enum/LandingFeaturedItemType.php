<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum LandingFeaturedItemType.
 *
 * Defines whether a landing page featured item points to a photo or an album.
 */
enum LandingFeaturedItemType: string
{
	case PHOTO = 'photo';
	case ALBUM = 'album';
}
