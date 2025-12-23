<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

use App\Enum\Traits\DecorateBackedEnum;
use App\Repositories\ConfigManager;

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
	case UNTAGGED = 'untagged';

	/**
	 * Return whether the smart album is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled(ConfigManager $config_manager): bool
	{
		return match ($this) {
			self::UNSORTED => $config_manager->getValueAsBool('enable_unsorted'),
			self::STARRED => $config_manager->getValueAsBool('enable_starred'),
			self::RECENT => $config_manager->getValueAsBool('enable_recent'),
			self::ON_THIS_DAY => $config_manager->getValueAsBool('enable_on_this_day'),
			self::UNTAGGED => $config_manager->getValueAsBool('enable_untagged'),
		};
	}
}