<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds `Clear-Site-Data: "cache"` to the response of a request during which
 * a user logged in or out, so the browser drops the privately cached assets
 * of the previous viewer (Feature 102, FR-102-08).
 */
class ClearBrowserCache
{
	/** Request attribute set by {@see \App\Listeners\ClearBrowserCacheOnAuthChange}. */
	public const FLAG = 'clear_browser_cache';

	public function handle(Request $request, \Closure $next): mixed
	{
		$response = $next($request);

		if ($request->attributes->get(self::FLAG) === true && $response instanceof Response) {
			$response->headers->set('Clear-Site-Data', '"cache"');
		}

		return $response;
	}
}
