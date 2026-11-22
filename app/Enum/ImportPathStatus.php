<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Result of {@see \App\Actions\Import\ImportPathGuard::check()}.
 */
enum ImportPathStatus: string
{
	/** The path exists and lies inside the import root. */
	case INSIDE = 'inside';
	/** The path lies outside the import root, whether it exists or not. */
	case OUTSIDE = 'outside';
	/** The path lies inside the import root (lexically) but does not exist. */
	case MISSING = 'missing';
}
