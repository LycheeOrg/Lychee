<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

enum MetricsAccess: string
{
	case PUBLIC = 'public';
	case LOGGED_IN = 'logged-in users';
	case OWNER = 'owner';
	case ADMIN = 'admin';
}
