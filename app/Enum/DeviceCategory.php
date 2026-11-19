<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Kind of capture device, as classified by Insights (Feature 085, FR-085-12).
 */
enum DeviceCategory: string
{
	case CAMERA = 'camera';
	case MOBILE = 'mobile';
	case OTHER = 'other';
}
