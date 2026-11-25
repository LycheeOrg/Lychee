<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

use App\Models\User;
use App\Policies\AlbumPolicy;

/**
 * Whether `lychee:sync` with no path syncs a user's folder (Feature 115, FR-115-13).
 */
enum SyncUserEligibility: string
{
	case ELIGIBLE = 'eligible';
	/** `server_path` is `null`: import from server disabled. */
	case NO_PATH = 'no path';
	/** `server_path` is `/`: there is no folder of their own to sync. */
	case FULL_ACCESS = 'full access';
	/** Feature disabled (Feature 118, FR-118-14: a server path no longer needs upload rights). */
	case NOT_ALLOWED = 'may not import';

	public static function of(User $user): self
	{
		return match (true) {
			$user->server_path === null => self::NO_PATH,
			$user->server_path === '/' => self::FULL_ACCESS,
			!(new AlbumPolicy())->canImportFromServer($user) => self::NOT_ALLOWED,
			default => self::ELIGIBLE,
		};
	}
}
