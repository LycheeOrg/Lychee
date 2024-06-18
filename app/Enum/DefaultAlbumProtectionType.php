<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Enum DefaultAlbumProtectionType.
 */
enum DefaultAlbumProtectionType: int
{
	case PRIVATE = 1;
	case PUBLIC = 2;
	case INHERIT = 3;
}
