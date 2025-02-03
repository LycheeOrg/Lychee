<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum ConfigType.
 *
 * The most important type possibilities.
 */
enum CacheTag: string
{
	case GALLERY = 'gallery';
	case AUTH = 'auth';
	case USER = 'user';
	case SETTINGS = 'settings';
	case STATISTICS = 'statistics';
	case USERS = 'users';
}