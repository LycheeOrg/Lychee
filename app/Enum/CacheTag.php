<?php

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