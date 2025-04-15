<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

enum MetricsAccess: string
{
	case PUBLIC = 'public';
	case LOGGEDIN = 'logged-in users';
	case OWNER = 'owner';
	case ADMIN = 'admin';
}
