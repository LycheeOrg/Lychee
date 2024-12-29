<?php

namespace App\Http\Middleware\Caching;

use App\Metadata\Cache\RouteCacheConfig;
use App\Metadata\Cache\RouteCacheManager;
use App\Models\Configs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Response caching, this allows to speed up the reponse time of Lychee by hopefully a lot.
 */
class ResponseCache
{
	private RouteCacheManager $route_cache_manager;

	public function __construct(RouteCacheManager $route_cache_manager)
	{
		$this->route_cache_manager = $route_cache_manager;
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

		$config = $this->route_cache_manager->get_config($request->route()->uri);

		// Check with the route manager if we can cache this route.
		if ($config === false) {
			return $next($request);
		}

		if (Cache::supportsTags()) {
			return $this->cacheWithTags($request, $next, $config);
		}

		return $this->chacheWithoutTags($request, $next, $config);
	}

	/**
	 * This is the light version of caching: we cache only if the user is not logged in.
	 *
	 * @param Request  $request
	 * @param \Closure $next
	 *
	 * @return mixed
	 */
	private function chacheWithoutTags(Request $request, \Closure $next, RouteCacheConfig $config): mixed
	{
		// We do not cache this.
		if ($config->user_dependant && Auth::user() !== null) {
			return $next($request);
		}

		$key = $this->route_cache_manager->get_key($request, $config);

		return Cache::remember($key, Configs::getValueAsInt('cache_ttl'), fn () => $next($request));
	}

	/**
	 * This is the stronger version of caching.
	 *
	 * @param Request  $request
	 * @param \Closure $next
	 *
	 * @return mixed
	 */
	private function cacheWithTags(Request $request, \Closure $next, RouteCacheConfig $config): mixed
	{
		$key = $this->route_cache_manager->get_key($request, $config);

		return Cache::tags([$config->tag])->remember($key, Configs::getValueAsInt('cache_ttl'), fn () => $next($request));
	}
}
