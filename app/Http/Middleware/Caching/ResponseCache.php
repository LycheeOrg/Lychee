<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware\Caching;

use App\Metadata\Cache\RouteCacheManager;
use App\Metadata\Cache\RouteCacher;
use App\Models\Configs;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Response caching, this allows to speed up the reponse time of Lychee by hopefully a lot.
 */
class ResponseCache
{
	public function __construct(
		private RouteCacheManager $route_cache_manager,
		private RouteCacher $route_cacher,
	) {
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param Request                                                                                           $request
	 * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
	 *
	 * @return Response
	 *
	 * @throws \InvalidArgumentException
	 */
	public function handle(Request $request, \Closure $next): mixed
	{
		// We only cache get requests.
		if ($request->method() !== 'GET') {
			return $next($request);
		}

		if (Configs::getValueAsBool('cache_enabled') === false) {
			return $next($request);
		}

		$uri = $request->route()->uri;
		$config = $this->route_cache_manager->get_config($uri);

		// Check with the route manager if we can cache this route.
		if ($config === false) {
			return $next($request);
		}

		$key = $this->route_cache_manager->get_key($request, $config);

		$extras = [];
		foreach ($config->extra as $extra) {
			$extras[] = $request->input($extra) ?? '';
		}

		return $this->route_cacher->remember($key, $uri, Configs::getValueAsInt('cache_ttl'), fn () => $next($request), $extras);
	}
}
