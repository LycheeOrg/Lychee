<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Cache;

use App\Exceptions\Internal\LycheeLogicException;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Generic, domain-agnostic memoize-with-tag-eviction service.
 *
 * Tags are not a native cache-store primitive (the default `file` driver has
 * no tagging support): a tag is itself a cache entry whose value is the set
 * of member keys currently associated with it, mirroring the pattern already
 * proven by `RouteCacher::rememberTags()`/`forgetTag()`, reimplemented here
 * independently so this service has no dependency on routes or requests.
 */
class ManagedCacheService
{
	public const TAG = 'MC:';

	public function __construct(
		protected ConfigManager $config_manager,
	) {
	}

	/**
	 * Get an item from the cache, or execute the given Closure and store the result.
	 *
	 * @template TCacheValue
	 *
	 * @param string                                    $key
	 * @param string[]                                  $tags
	 * @param \DateTimeInterface|\DateInterval|int|null $ttl
	 * @param \Closure(): TCacheValue                   $callback
	 *
	 * @return TCacheValue
	 */
	public function remember(
		string $key,
		array $tags,
		\DateTimeInterface|\DateInterval|int|null $ttl,
		\Closure $callback,
	): mixed {
		if (!$this->config_manager->getValueAsBool('managed_cache_enabled')) {
			return $callback();
		}

		$value = Cache::get($key);
		if (!is_null($value)) {
			return $value;
		}

		$value = $callback();
		try {
			Cache::put($key, $value, $ttl);
			$this->rememberTags($tags, $key);
			// @codeCoverageIgnoreStart
		} catch (\Exception $e) {
			// If we can't cache the value, we will just return the value.
			Log::error(__METHOD__ . ':' . __LINE__ . ' Could not cache the value.', ['exception' => $e]);
		}
		// @codeCoverageIgnoreEnd

		return $value;
	}

	/**
	 * Associate additional tags with an already-cached key, without recomputing
	 * or re-storing its value.
	 *
	 * Useful when the full set of tags a value depends on can only be known
	 * after the value itself has been computed (e.g. tagging a cached listing
	 * with the id of every item it currently contains, alongside the parent
	 * tag known up-front via {@see self::remember()}). A no-op if the key is
	 * not currently cached (e.g. the managed cache is disabled, or the entry
	 * has already expired).
	 *
	 * @param string   $key
	 * @param string[] $tags
	 *
	 * @return void
	 */
	public function addTags(string $key, array $tags): void
	{
		if (!$this->config_manager->getValueAsBool('managed_cache_enabled')) {
			return;
		}

		if (is_null(Cache::get($key))) {
			return;
		}

		$this->rememberTags($tags, $key);
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
	 * Remember the tags for the given key.
	 * This allows to later erase all the keys related to a tag (e.g. an album id).
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
