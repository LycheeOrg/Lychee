<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

enum FaceScanStatus: string
{
	case PENDING = 'pending';
	case COMPLETED = 'completed';
	case FAILED = 'failed';
}
