<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Assets\Features;
use Illuminate\Http\Request;

/**
 * Class RestrictApiDocsAccess.
 *
 * White label installs must not expose the existence of the API docs,
 * so the route is hidden entirely (404) instead of merely unauthorized.
 */
class RestrictApiDocsAccess
{
	/**
	 * Handle an incoming request.
	 *
	 * @param Request  $request the incoming request to serve
	 * @param \Closure $next    the next operation to be applied to the request
	 */
	public function handle(Request $request, \Closure $next): mixed
	{
		if (Features::active('white_label_enabled')) {
			abort(404);
		}

		return $next($request);
	}
}
