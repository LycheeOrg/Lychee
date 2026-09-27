<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Exceptions\GalleryPasswordRequiredException;
use App\Services\GalleryLockState;
use Illuminate\Http\Request;

/**
 * Refuses every request of an anonymous visitor who has not unlocked the
 * gallery while a gallery password is set.
 *
 * Registered in the `api` middleware group: routes that must stay reachable
 * while the gallery is locked opt out with `->withoutMiddleware('gallery_password')`.
 */
class GalleryPasswordRequired
{
	/**
	 * @throws GalleryPasswordRequiredException
	 */
	public function handle(Request $request, \Closure $next): mixed
	{
		if (GalleryLockState::isLockedForRequest($request)) {
			throw new GalleryPasswordRequiredException();
		}

		return $next($request);
	}
}
