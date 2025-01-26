<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;

/**
 * Add middleware that introduces latency to the request.
 *
 * It is possible to enable network throttling in web browsers.
 * However this applies to all requests, including loading images, css, js, etc.
 *
 * We want to be able to test the front-end when API is slow but other components are already loaded.
 * This middleware adds a small delay before executing the request.
 */
class Latency
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request                                                                          $request
	 * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
	 *
	 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
	 */
	public function handle(Request $request, \Closure $next)
	{
		$latency = config('features.latency');

		if ($latency > 0) {
			usleep($latency * 1000);
		}

		return $next($request);
	}
}