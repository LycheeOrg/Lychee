<?php

namespace App\Enum;

/**
 * Enum SmartAlbumType.
 */
enum SmartAlbumType: string
{
	use DecorateBackedEnum;

	case UNSORTED = 'unsorted';
	case STARRED = 'starred';
	case RECENT = 'recent';
	case PUBLIC = 'public';
	case ON_THIS_DAY = 'on_this_day';
}