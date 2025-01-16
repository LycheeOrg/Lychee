<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Metadata\Cache;

use App\Exceptions\Internal\LycheeLogicException;
use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * RouteCacher also associate the route data with the cache key.
 * That way it is easier to erase the associated cache keys when the route is updated.
 */
class RouteCacher
{
	public const TAG = 'T:';
	public const ROUTE = 'R:';

	/**
	 * Get an item from the cache, or execute the given Closure and store the result.
	 *
	 * @template TCacheValue
	 *
	 * @param string                                    $key
	 * @param string                                    $route
	 * @param \DateTimeInterface|\DateInterval|int|null $ttl
	 * @param \Closure(): TCacheValue                   $callback
	 * @param string[]                                  $tags
	 *
	 * @return TCacheValue
	 */
	public function remember(
		string $key,
		string $route,
		\DateTimeInterface|\DateInterval|int|null $ttl,
		\Closure $callback,
		array $tags,
	): mixed {
		$value = Cache::get($key);

		// If the item exists in the cache we will just return this immediately and if
		// not we will execute the given Closure and cache the result of that for a
		// given number of seconds so it's available for all subsequent requests.
		if (!is_null($value)) {
			return $value;
		}

		$value = $callback();
		Cache::put($key, $value, $ttl);

		// Update the list of keys for the given route.
		$this->rememberRoute($route, $key);

		// Update the tags for the given key.
		$this->rememberTags($tags, $key);

		return $value;
	}

	/**
	 * Forget all the keys related to the given route.
	 *
	 * @param string $route
	 *
	 * @return void
	 */
	public function forgetRoute(string $route): void
	{
		$keys = Cache::get($route, []);

		foreach (array_keys($keys) as $key) {
			if (!is_string($key)) {
				throw new LycheeLogicException('The keys should be a string');
			}

			Cache::forget($key);
		}

		Cache::forget($route);
	}

	/**
	 * Forget all the keys related to the given tag.
	 *
	 * @param string $tag
	 *
	 * @return void
	 */
	public function forgetTag(string $tag): void
	{
		$keys = Cache::get(self::TAG . $tag, []);

		foreach (array_keys($keys) as $key) {
			if (!is_string($key)) {
				throw new LycheeLogicException('The keys should be a string');
			}

			Cache::forget($key);
		}

		Cache::forget(self::TAG . $tag);
	}

	/**
	 * Remember the route for the given key.
	 * This allows to later erase all the keys related to the route.
	 *
	 * @param string $route
	 * @param string $key
	 *
	 * @return void
	 */
	private function rememberRoute(string $route, string $key): void
	{
		$already_cached_for_routes = Cache::get($route, []);
		$already_cached_for_routes[$key] = true;
		Cache::put($route, $already_cached_for_routes);
	}

	/**
	 * This is like the function above: rememberRoute() but with specific tags.
	 * That way we can later erase all the keys related to the tag (e.g. the album id).
	 *
	 * @param string[] $tags
	 * @param string   $key
	 *
	 * @return void
	 */
	private function rememberTags(array $tags, string $key): void
	{
		foreach ($tags as $tag) {
			$already_cached_for_tag = Cache::get(self::TAG . $tag, []);
			$already_cached_for_tag[$key] = true;
			Cache::put(self::TAG . $tag, $already_cached_for_tag);
		}
	}
}