<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Services\Auth\LoginThrottleService;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Throttle of `POST /api/v2/Auth::login` (Feature 112, FR-112-01): 10
 * attempts per IP in a 60-minute window, then 429 until the window ends.
 *
 * It replaces Laravel's `throttle:10,60,login` so that blocked IPs can be
 * listed and unbanned with `php artisan lychee:blocked-ips` (ADR-112-01).
 * A blocked request only reads the cache.
 */
class ThrottleLogin
{
	public function __construct(
		private LoginThrottleService $throttle,
	) {
	}

	/**
	 * @param Request                     $request
	 * @param \Closure(Request): Response $next
	 *
	 * @return Response
	 *
	 * @throws ThrottleRequestsException
	 */
	public function handle(Request $request, \Closure $next): Response
	{
		$ip = $request->ip() ?? '';
		if ($this->throttle->isBlocked($ip)) {
			throw new ThrottleRequestsException('Too Many Attempts.', null, $this->throttle->blockedHeaders($ip));
		}

		$this->throttle->hit($ip);
		$response = $next($request);
		$response->headers->add($this->throttle->passHeaders($ip));

		return $response;
	}
}
