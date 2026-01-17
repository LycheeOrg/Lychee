<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

use App\Enum\Traits\DecorateBackedEnum;
use App\Repositories\ConfigManager;
use LycheeVerify\Contract\VerifyInterface;

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

	// Rating-based smart albums (Feature 009)
	case UNRATED = 'unrated';
	case ONE_STAR = 'one_star';
	case TWO_STARS = 'two_stars';
	case THREE_STARS = 'three_stars';
	case FOUR_STARS = 'four_stars';
	case FIVE_STARS = 'five_stars';
	case BEST_PICTURES = 'best_pictures';

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
			// Rating-based smart albums (Feature 009)
			self::UNRATED => $config_manager->getValueAsBool('enable_unrated'),
			self::ONE_STAR => $config_manager->getValueAsBool('enable_1_star'),
			self::TWO_STARS => $config_manager->getValueAsBool('enable_2_stars'),
			self::THREE_STARS => $config_manager->getValueAsBool('enable_3_stars'),
			self::FOUR_STARS => $config_manager->getValueAsBool('enable_4_stars'),
			self::FIVE_STARS => $config_manager->getValueAsBool('enable_5_stars'),
			// Best Pictures requires both config AND Lychee SE license
			self::BEST_PICTURES => $config_manager->getValueAsBool('enable_best_pictures') && $this->isLycheeSEActive(),
		};
	}

	/**
	 * Check if Lychee SE is activated.
	 * Uses the app container to resolve the Verify service.
	 */
	private function isLycheeSEActive(): bool
	{
		try {
			$verify = app(VerifyInterface::class);

			return $verify->is_supporter();
		} catch (\Throwable) {
			// If verification service is not available, default to disabled
			return false;
		}
	}
}