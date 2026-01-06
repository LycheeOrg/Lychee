<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

enum ImageType: int
{
	case JPEG = IMAGETYPE_JPEG;
	case JPEG2000 = IMAGETYPE_JPEG2000;
	case PNG = IMAGETYPE_PNG;
	case GIF = IMAGETYPE_GIF;
	case WEBP = IMAGETYPE_WEBP;
}

