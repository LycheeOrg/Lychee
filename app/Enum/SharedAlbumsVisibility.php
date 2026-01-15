<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum SharedAlbumsVisibility.
 *
 * Server-level visibility modes for shared albums in the gallery.
 */
enum SharedAlbumsVisibility: string
{
	case SHOW = 'show';
	case SEPARATE = 'separate';
	case SEPARATE_SHARED_ONLY = 'separate_shared_only';
	case HIDE = 'hide';
}
