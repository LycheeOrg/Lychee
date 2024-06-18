<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Enum ThumbOverlayVisibilityType.
 *
 * All the allowed display possibilities of the overlay on thumbs
 */
enum ThumbOverlayVisibilityType: string
{
	case NEVER = 'never';
	case ALWAYS = 'always';
	case HOVER = 'hover';
}
