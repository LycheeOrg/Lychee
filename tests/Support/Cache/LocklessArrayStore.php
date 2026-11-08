<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Support\Cache;

use Illuminate\Cache\ArrayStore;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * An in-memory cache store without lock support, standing in for `apc`
 * (Feature 112, FX-112-01).
 */
final class LocklessArrayStore implements Store
{
	private ArrayStore $inner;

	public function __construct()
	{
		$this->inner = new ArrayStore();
	}

	/**
	 * Register the store as `lockless` and make it the rate limiter's store.
	 */
	public static function register(): void
	{
		// `extend()` rebinds the closure to the cache manager, so `self` would be the manager.
		Cache::extend('lockless', fn () => Cache::repository(new LocklessArrayStore()));
		Config::set('cache.stores.lockless', ['driver' => 'lockless']);
		Config::set('cache.limiter', 'lockless');
	}

	public function get($key)
	{
		return $this->inner->get($key);
	}

	public function many(array $keys)
	{
		return $this->inner->many($keys);
	}

	public function put($key, $value, $seconds)
	{
		return $this->inner->put($key, $value, $seconds);
	}

	public function putMany(array $values, $seconds)
	{
		return $this->inner->putMany($values, $seconds);
	}

	public function increment($key, $value = 1)
	{
		return $this->inner->increment($key, $value);
	}

	public function decrement($key, $value = 1)
	{
		return $this->inner->decrement($key, $value);
	}

	public function forever($key, $value)
	{
		return $this->inner->forever($key, $value);
	}

	public function touch($key, $seconds)
	{
		return $this->inner->touch($key, $seconds);
	}

	public function forget($key)
	{
		return $this->inner->forget($key);
	}

	public function flush()
	{
		return $this->inner->flush();
	}

	public function getPrefix()
	{
		return $this->inner->getPrefix();
	}
}
