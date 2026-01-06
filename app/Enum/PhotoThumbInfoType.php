<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum PhotoThumbInfoType.
 *
 * All the allowed possibilities of info shown on photo thumb
 */
enum PhotoThumbInfoType: string
{
	case TITLE = 'title';
	case DESCRIPTION = 'description';
}
