<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum SeverityType.
 *
 * The type of severity that are being issued.
 * We do not use integers as the Enum id is taking care of that,
 * the value makes sure that we stays consistent with the database.
 */
enum SeverityType: string
{
	case EMERGENCY = 'emergency'; // 0
	case ALERT = 'alert'; // 1
	case CRITICAL = 'critical'; // 2
	case ERROR = 'error'; // 3
	case WARNING = 'warning'; // 4
	case NOTICE = 'notice'; // 5
	case INFO = 'info'; // 6
	case DEBUG = 'debug'; // 7
}