<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;

/**
 * Forces the request to report `application/json` as an acceptable content
 * type, so that any exception thrown further down the pipeline (validation,
 * authorization, model-not-found, etc.) is rendered as Lychee's standard
 * JSON error body, regardless of what the actual client sent as its `Accept`
 * header.
 *
 * Needed for endpoints (e.g. Feature 056's binary asset passthrough) which
 * intentionally opt out of the `accept_content_type:json` gate because a
 * *successful* response is not JSON, but whose *error* responses still must
 * be (FR-056-02).
 */
class EnsureJsonErrorResponses
{
	public function handle(Request $request, \Closure $next): mixed
	{
		$request->headers->set('Accept', 'application/json');

		return $next($request);
	}
}
