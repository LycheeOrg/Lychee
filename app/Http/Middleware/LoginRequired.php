<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Exceptions\ConfigurationException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\UnauthenticatedException;
use App\Models\Configs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class LoginRequired.
 *
 * This middleware is ensures that only logged in users can access Lychee.
 */
class LoginRequired
{
	public const ROOT = 'root';
	public const ALBUM = 'album';
	public const ALWAYS = 'always';

	/**
	 * Handle an incoming request.
	 *
	 * @param Request  $request        the incoming request to serve
	 * @param \Closure $next           the next operation to be applied to the
	 *                                 request
	 * @param string   $requiredStatus the required login status; either
	 *                                 {@link self::ROOT} or
	 *                                 {@link self::ALBUM}
	 *
	 * @throws ConfigurationException
	 * @throws FrameworkException
	 */
	public function handle(Request $request, \Closure $next, string $requiredStatus): mixed
	{
		if (in_array($requiredStatus, [self::ALBUM, self::ROOT, self::ALWAYS], true) === false) {
			throw new LycheeInvalidArgumentException($requiredStatus . ' is not a valid login requirement.');
		}

		// We are logged in. Proceed.
		if (Auth::user() !== null) {
			return $next($request);
		}

		if ($requiredStatus === self::ALWAYS) {
			return redirect()->route('gallery');
		}

		if (!Configs::getValueAsBool('login_required')) {
			// Login is not required. Proceed.
			return $next($request);
		}

		if ($requiredStatus === self::ALBUM && Configs::getValueAsBool('login_required_root_only')) {
			return $next($request);
		}

		throw new UnauthenticatedException('Login required.');
	}
}
