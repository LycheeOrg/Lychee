<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum CountType.
 *
 * The type of counting for the punch card data.
 */
enum CountType: string
{
	case TAKEN_AT = 'taken_at';
	case CREATED_AT = 'created_at';
}