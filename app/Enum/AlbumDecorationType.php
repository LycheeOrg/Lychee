<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum AlbumDecorationType.
 *
 * All the allowed sorting possibilities on Album
 */
enum AlbumDecorationType: string
{
	case NONE = 'none';
	case LAYERS = 'layers';
	case ALBUM = 'album';
	case PHOTO = 'photo';
	case ALL = 'all';
}
