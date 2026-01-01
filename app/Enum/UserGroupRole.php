<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum representing the roles a user can have in a user group.
 */
enum UserGroupRole: string
{
	case MEMBER = 'member';
	case ADMIN = 'admin';
}
