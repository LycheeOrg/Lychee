<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

enum MessageType: string
{
	case INFO = 'info';
	case WARNING = 'warning';
	case ERROR = 'error';
}