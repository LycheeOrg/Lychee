<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum VisibilityType.
 *
 * All the allowed visibility modes for UI elements
 */
enum VisibilityType: string
{
	case NEVER = 'never';
	case ALWAYS = 'always';
	case HOVER = 'hover';
}
