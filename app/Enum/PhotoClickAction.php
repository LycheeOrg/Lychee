<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum PhotoClickAction.
 *
 * What a click on the photo does in the v8 lightbox.
 */
enum PhotoClickAction: string
{
	case OVERLAY = 'overlay';
	case ZOOM = 'zoom';
}
