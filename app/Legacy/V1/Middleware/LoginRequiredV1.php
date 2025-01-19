<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Middleware;

use App\Exceptions\ConfigurationException;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Models\Configs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Class LoginRequiredV1.
 *
 * This middleware is ensures that only logged in users can access Lychee.
 */
final class LoginRequiredV1
{
	public const ROOT = 'root';
	public const ALBUM = 'album';

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
	 *
	 * @codeCoverageIgnore Legacy stuff we don't care.
	 */
	public function handle(Request $request, \Closure $next, string $requiredStatus): mixed
	{
		// We are logged in. Proceed.
		if (Auth::user() !== null) {
			return $next($request);
		}

		if ($requiredStatus !== self::ALBUM && $requiredStatus !== self::ROOT) {
			throw new LycheeLogicException($requiredStatus . ' is not a valid login requirement.');
		}

		try {
			if (!Configs::getValueAsBool('login_required')) {
				// Login is not required. Proceed.
				return $next($request);
			}

			if ($requiredStatus === self::ALBUM && Configs::getValueAsBool('login_required_root_only')) {
				return $next($request);
			}

			return redirect()->route('login');
		} catch (ConfigurationKeyMissingException $e) {
			Log::warning(__METHOD__ . ':' . __LINE__ . ' ' . $e->getMessage());

			return $next($request);
		}
	}
}
