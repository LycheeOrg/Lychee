<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

use App\Repositories\ConfigManager;

/**
 * Enum UserSharedAlbumsVisibility.
 *
 * User-level preference for shared albums visibility.
 * Includes DEFAULT option to inherit from server configuration.
 */
enum UserSharedAlbumsVisibility: string
{
	case DEFAULT = 'default';
	case SHOW = 'show';
	case SEPARATE = 'separate';
	case SEPARATE_SHARED_ONLY = 'separate_shared_only';
	case HIDE = 'hide';

	public function tooSharedAlbumsVisibility(): SharedAlbumsVisibility
	{
		return match ($this) {
			self::SHOW => SharedAlbumsVisibility::SHOW,
			self::SEPARATE => SharedAlbumsVisibility::SEPARATE,
			self::SEPARATE_SHARED_ONLY => SharedAlbumsVisibility::SEPARATE_SHARED_ONLY,
			self::HIDE => SharedAlbumsVisibility::HIDE,
			self::DEFAULT => resolve(ConfigManager::class)->getValueAsEnum('shared_albums_visibility', SharedAlbumsVisibility::class),
		};
	}
}
