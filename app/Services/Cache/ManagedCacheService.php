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
	 * @param \Closure(): TCacheValue                   $callback
	 * @param \Closure(TCacheValue): string[]|null      $extra_tags computes additional tags from the freshly computed
	 *                                                              value (e.g. the id of each item it contains). Only
	 *                                                              invoked on a cache miss, since a cache hit's value
	 *                                                              is already tagged from when it was first stored —
	 *                                                              avoids the extra cache round trips a separate
	 *                                                              {@see self::addTags()} call on every hit would cost.
	 * @param \DateTimeInterface|\DateInterval|int|null $ttl        when `null`, falls back to the `managed_cache_ttl` config value
	 *
	 * @return TCacheValue
	 */
	public function remember(
		string $key,
		array $tags,
		\Closure $callback,
		\Closure|null $extra_tags = null,
		\DateTimeInterface|\DateInterval|int|null $ttl = null,
	): mixed {
		// Fully skip if the managed cache is disabled, this is a safe no-op to ensure
		// there is a fallback if things go south.
		if (config('features.enable-caching') === false) {
			return $callback();
		}

		// Fully skip if the managed cache is disabled in settings
		if (!$this->config_manager->getValueAsBool('managed_cache_enabled')) {
			return $callback();
		}

		$ttl ??= $this->config_manager->getValueAsInt('managed_cache_ttl');

		try {
			$value = Cache::get($key);
			if (!is_null($value)) {
				return $value;
			}
		} catch (\Exception $e) {
			// If we can't read the cache, we will just compute the value.
			Log::error(__METHOD__ . ':' . __LINE__ . ' Could not read the cache.', ['exception' => $e]);
		}

		$value = $callback();
		try {
			Cache::put($key, $value, $ttl);
			$all_tags = $extra_tags === null ? $tags : [...$tags, ...$extra_tags($value)];
			$this->rememberTags($all_tags, $key);
			// @codeCoverageIgnoreStart
		} catch (\Exception $e) {
			// If we can't cache the value, we will just return the value.
			Log::error(__METHOD__ . ':' . __LINE__ . ' Could not cache the value.', ['exception' => $e]);
		}
		// @codeCoverageIgnoreEnd

		return $value;
	}

	/**
	 * Like {@see self::remember()}, but conditionally: when `$condition` is
	 * `false`, invokes and returns the callback directly, with no cache I/O
	 * attempted at all (not even a read probe). When `true`, delegates
	 * unchanged to {@see self::remember()} (which still separately checks
	 * `managed_cache_enabled` — both switches are ANDed, neither overrides
	 * the other).
	 *
	 * @template TCacheValue
	 *
	 * @param bool                                      $condition
	 * @param string                                    $key
	 * @param string[]                                  $tags
	 * @param \Closure(): TCacheValue                   $callback
	 * @param \Closure(TCacheValue): string[]|null      $extra_tags see {@see self::remember()}
	 * @param \DateTimeInterface|\DateInterval|int|null $ttl        when `null`, falls back to the `managed_cache_ttl` config value
	 *
	 * @return TCacheValue
	 */
	public function rememberIf(
		bool $condition,
		string $key,
		array $tags,
		\Closure $callback,
		\Closure|null $extra_tags = null,
		\DateTimeInterface|\DateInterval|int|null $ttl = null,
	): mixed {
		if (!$condition) {
			return $callback();
		}

		return $this->remember($key, $tags, $callback, $extra_tags, $ttl);
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
		$this->forgetTags([$tag]);
	}

	/**
	 * Forget all the keys related to each of the given tags.
	 *
	 * Keys are aggregated across all tags first (de-duplicating any key
	 * shared by more than one tag) and forgotten in a single pass, before
	 * the tags themselves are forgotten.
	 *
	 * @param string[] $tags
	 *
	 * @return void
	 */
	public function forgetTags(array $tags): void
	{
		$keys_to_forget = [];
		try {
			foreach ($tags as $tag) {
				$keys = Cache::get(self::TAG . $tag, []);

				foreach (array_keys($keys) as $key) {
					if (!is_string($key)) {
						throw new LycheeLogicException('The keys should be a string');
					}

					$keys_to_forget[$key] = true;
				}
			}

			foreach (array_keys($keys_to_forget) as $key) {
				Cache::forget($key);
			}

			foreach ($tags as $tag) {
				Cache::forget(self::TAG . $tag);
			}
		} catch (LycheeLogicException $e) {
			throw $e;
		} catch (\Exception $e) {
			Log::error(__METHOD__ . ':' . __LINE__ . ' Could not invalidate the cache tags.', ['exception' => $e, 'tags' => $tags]);
		}
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
