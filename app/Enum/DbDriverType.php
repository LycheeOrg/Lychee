<?php

declare(strict_types=1);

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
