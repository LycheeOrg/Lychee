<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

use App\Enum\Traits\DecorateBackedEnum;
use App\Models\Configs;

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
	 * Return whether the smart album is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool
	{
		return match ($this) {
			self::UNSORTED => Configs::getValueAsBool('enable_unsorted'),
			self::STARRED => Configs::getValueAsBool('enable_starred'),
			self::RECENT => Configs::getValueAsBool('enable_recent'),
			self::ON_THIS_DAY => Configs::getValueAsBool('enable_on_this_day'),
		};
	}
}