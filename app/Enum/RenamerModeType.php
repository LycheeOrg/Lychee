<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum RenamerModeType.
 *
 * The type of renamer mode that are being used.
 * - First mode replaces only the first occurrence of the needle.
 * - All mode replaces all occurrences of the needle.
 * - Regex mode uses regular expressions for matching and replacing.
 */
enum RenamerModeType: string
{
	case FIRST = 'first';
	case ALL = 'all';
	case REGEX = 'regex';
	case TRIM = 'trim';
	case LOWER = 'strtolower';
	case UPPER = 'strtoupper';
	case UCWORDS = 'ucwords';
	case UCFIRST = 'ucfirst';
}