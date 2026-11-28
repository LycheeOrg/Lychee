<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use App\Models\Photo;
use App\Models\User;

/**
 * Whether a user owns at least one photo, memoised for the request
 * (Feature 118, FR-118-16, NFR-118-03).
 *
 * Registered as a scoped binding: Octane / FrankenPHP worker mode
 * flushes it between requests, so no state leaks across them.
 */
final class OwnedPhotoCheck
{
	/** @var array<int,bool> */
	private array $owns = [];

	public function ownsAnyPhoto(User $user): bool
	{
		return $this->owns[$user->id] ??= Photo::query()->where('owner_id', '=', $user->id)->exists();
	}
}
