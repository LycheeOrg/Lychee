<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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