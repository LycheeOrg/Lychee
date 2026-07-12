<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Assets\Features;
use Dedoc\Scramble\Generator;
use Dedoc\Scramble\Scramble;
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

		$this->ensureDocumentationIsCached();

		return $next($request);
	}

	/**
	 * Warm the OpenAPI document cache on demand if it is not already cached.
	 */
	private function ensureDocumentationIsCached(): void
	{
		$store = config('scramble.cache.store');
		$keyBase = config('scramble.cache.key');

		if ($store === null || $keyBase === null) {
			return;
		}

		$cache = cache()->store($store);
		$key = $keyBase . ':' . Scramble::DEFAULT_API;

		if ($cache->has($key)) {
			return;
		}

		$config = Scramble::getGeneratorConfig(Scramble::DEFAULT_API);
		$generator = app(Generator::class);

		$cache->forever($key, $generator($config));
	}
}
