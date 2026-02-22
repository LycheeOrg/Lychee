<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

use App\Enum\Traits\DecorateBackedEnum;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Auth;
use LycheeVerify\Contract\VerifyInterface;

/**
 * Enum SmartAlbumType.
 */
enum SmartAlbumType: string
{
	use DecorateBackedEnum;

	case UNSORTED = 'unsorted';
	case HIGHLIGHTED = 'highlighted';
	case RECENT = 'recent';
	case ON_THIS_DAY = 'on_this_day';
	case UNTAGGED = 'untagged';
	case UNRATED = 'unrated';
	case ONE_STAR = 'one_star';
	case TWO_STARS = 'two_stars';
	case THREE_STARS = 'three_stars';
	case FOUR_STARS = 'four_stars';
	case FIVE_STARS = 'five_stars';
	case BEST_PICTURES = 'best_pictures';
	case MY_RATED_PICTURES = 'my_rated_pictures';
	case MY_BEST_PICTURES = 'my_best_pictures';

	/**
	 * Return whether the smart album is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled(ConfigManager $config_manager): bool
	{
		return match ($this) {
			self::UNSORTED => $config_manager->getValueAsBool('enable_unsorted'),
			self::HIGHLIGHTED => $config_manager->getValueAsBool('enable_highlighted'),
			self::RECENT => $config_manager->getValueAsBool('enable_recent'),
			self::ON_THIS_DAY => $config_manager->getValueAsBool('enable_on_this_day'),
			self::UNTAGGED => $config_manager->getValueAsBool('enable_untagged'),
			self::UNRATED => $config_manager->getValueAsBool('enable_unrated'),
			self::ONE_STAR => $config_manager->getValueAsBool('enable_1_star'),
			self::TWO_STARS => $config_manager->getValueAsBool('enable_2_stars'),
			self::THREE_STARS => $config_manager->getValueAsBool('enable_3_stars'),
			self::FOUR_STARS => $config_manager->getValueAsBool('enable_4_stars'),
			self::FIVE_STARS => $config_manager->getValueAsBool('enable_5_stars'),
			// Best Pictures requires both config AND Lychee SE license
			self::BEST_PICTURES => $config_manager->getValueAsBool('enable_best_pictures') && $this->isLycheeSEActive(),
			// My Rated Pictures shows all photos the user has rated (authenticated users only)
			self::MY_RATED_PICTURES => Auth::check() && $config_manager->getValueAsBool('enable_my_rated_pictures'),
			// My Best Pictures requires authenticated user, config, AND Lychee SE license
			self::MY_BEST_PICTURES => Auth::check() && $config_manager->getValueAsBool('enable_my_best_pictures') && $this->isLycheeSEActive(),
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

	/**
	 * Whether the album requires the user to have upload rights.
	 *
	 * @return bool
	 */
	public function require_upload_rights(): bool
	{
		return match ($this) {
			self::UNSORTED,
			self::HIGHLIGHTED,
			self::RECENT,
			self::ON_THIS_DAY,
			self::UNRATED,
			self::UNTAGGED => true,
			self::ONE_STAR,
			self::TWO_STARS,
			self::THREE_STARS,
			self::FOUR_STARS,
			self::FIVE_STARS,
			self::BEST_PICTURES,
			self::MY_RATED_PICTURES,
			self::MY_BEST_PICTURES => false,
		};
	}
}