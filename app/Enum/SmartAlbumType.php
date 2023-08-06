<?php

namespace App\Enum;

use App\Enum\Traits\DecorateBackedEnum;

/**
 * Enum SmartAlbumType.
 */
enum SmartAlbumType: string
{
	use DecorateBackedEnum;

	case UNSORTED = 'unsorted';
	case PUBLIC = 'public';
	case STARRED = 'starred';
	case RECENT = 'recent';
	case ON_THIS_DAY = 'on_this_day';
}