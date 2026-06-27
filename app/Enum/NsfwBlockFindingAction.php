<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

enum NsfwBlockFindingAction: string
{
	case BLOCK = 'block';
	case MODERATE = 'moderate';
	case APPROVE = 'approve';
}
