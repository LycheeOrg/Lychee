<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class CacheControl
{
	/**
	 * Handle an incoming request.
	 *
	 * Without an age, the browser may keep the response but must revalidate
	 * it before reuse: these responses depend on the viewer's identity, which
	 * the URL does not capture, so reusing them as-is would leak across a
	 * login/logout in the same browser (CWE-524). An explicit age is only for
	 * identity-independent routes (e.g. the session-less embed API).
	 *
	 * @param \Illuminate\Http\Request                                                                          $request
	 * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
	 * @param string|null                                                                                       $age     Duration in seconds the response may be reused without revalidation
	 *
	 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
	 */
	public function handle(Request $request, \Closure $next, ?string $age = null)
	{
		$response = $next($request);
		$response->headers->set('Cache-Control', $age === null ? 'private, no-cache' : 'private, max-age=' . $age);

		return $response;
	}
}