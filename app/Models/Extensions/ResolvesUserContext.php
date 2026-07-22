<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models\Extensions;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Lets a photo-query class (smart album, tag/person relation, ...) be
 * evaluated "as seen by" an explicit user instead of the currently
 * authenticated one.
 *
 * Used by {@link \App\Jobs\RecomputeAlbumUserThumbsJob} to compute a
 * tag/person/smart album's thumb outside of an HTTP request, where there is
 * no authenticated user to fall back on.
 */
trait ResolvesUserContext
{
	/**
	 * Explicit user context, overriding the currently authenticated user.
	 * Null value + $user_is_set=true means "as seen by a guest".
	 */
	protected ?User $for_user = null;
	protected bool $user_is_set = false;

	/**
	 * Resolves the user whose permissions should be used to query photos:
	 * the explicit override, or the currently authenticated user otherwise.
	 */
	protected function resolveUser(): ?User
	{
		return $this->user_is_set ? $this->for_user : Auth::user();
	}
}
