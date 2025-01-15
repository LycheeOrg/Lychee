<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum DbDriverType.
 *
 * All the kind of DB supported
 */
enum DbDriverType: string
{
	case MYSQL = 'mysql';
	case PGSQL = 'pgsql';
	case SQLITE = 'sqlite';
}
