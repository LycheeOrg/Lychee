<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Exceptions\UnauthorizedException;
use App\Http\Requests\Gallery\UnlockGalleryRequest;
use App\Services\GalleryLockState;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

/**
 * Unlocks the gallery for an anonymous visitor who knows the gallery password.
 *
 * This is the only place where the gallery password is verified with bcrypt.
 * Every other request only compares the fingerprint held by the unlock cookie.
 */
class GalleryUnlockController extends Controller
{
	/**
	 * @throws UnauthorizedException
	 */
	public function unlock(UnlockGalleryRequest $request): void
	{
		$stored_hash = $request->configs()->getValueAsString(GalleryLockState::CONFIG_KEY);
		if ($stored_hash === '') {
			return;
		}

		if (!Hash::check($request->password(), $stored_hash)) {
			throw new UnauthorizedException('Password is invalid');
		}

		Cookie::queue(GalleryLockState::makeUnlockCookie(
			$stored_hash,
			$request->configs()->getValueAsInt(GalleryLockState::LIFETIME_CONFIG_KEY),
			$request->isSecure(),
			time(),
		));
	}
}
