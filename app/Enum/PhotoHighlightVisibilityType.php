<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum PhotoHighlightVisibilityType.
 *
 * Options used to configure visibility of star flag in photos.
 */
enum PhotoHighlightVisibilityType: string
{
	case ANONYMOUS = 'anonymous';
	case AUTHENTICATED = 'authenticated';
	case EDITOR = 'editor';
}
