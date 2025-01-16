<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware\Caching;

use Illuminate\Http\Request;

class CacheControl
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request                                                                          $request
	 * @param string                                                                                            $age     Duration in second of the cache
	 * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
	 *
	 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
	 */
	public function handle(Request $request, \Closure $next, string $age = '3600')
	{
		$response = $next($request);
		$response->headers->set('Cache-Control', 'private;max_age=' . $age);

		return $response;
	}
}