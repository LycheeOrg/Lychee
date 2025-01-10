<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum ThumbAlbumSubtitleType.
 *
 * The kind of info displayed under the title in the thumb.
 */
enum ThumbAlbumSubtitleType: string
{
	case DESCRIPTION = 'description';
	case TAKEDATE = 'takedate';
	case CREATION = 'creation';
	case OLDSTYLE = 'oldstyle';
}