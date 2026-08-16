<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Which frame of a video is used to generate its thumbnail.
 */
enum VideoThumbnailFrameMode: string
{
	case FIRST = 'first';
	case MIDDLE = 'middle';
	case CUSTOM = 'custom';
}
