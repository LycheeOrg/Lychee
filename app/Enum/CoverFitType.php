<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum CoverFitType.
 *
 * This defines whether a photo fits or cover its container.
 */
enum CoverFitType: string
{
	case COVER = 'cover';
	case FIT = 'fit';
}