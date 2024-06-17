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
	case STARRED = 'starred';
	case RECENT = 'recent';
	case ON_THIS_DAY = 'on_this_day';

	/**
	 * Given a SmartAlbumType return the associated config key.
	 *
	 * @return string
	 */
	public function get_config_key(): string
	{
		return match ($this) {
			self::UNSORTED => 'enable_unsorted',
			self::STARRED => 'enable_starred',
			self::RECENT => 'enable_recent',
			self::ON_THIS_DAY => 'enable_on_this_day',
		};
	}
}