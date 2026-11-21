<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Cache;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Cache store that the web and the worker containers both see (ADR-116-01, ADR-090-01).
 *
 * The default store, unless a worker runs (queue not `sync`) and the default
 * driver keeps its data inside one container: then the `database` store.
 */
final class SharedCacheStore
{
	/**
	 * Cache drivers whose data stays inside one container.
	 */
	public const PER_CONTAINER_DRIVERS = ['file', 'array', 'apc'];

	/**
	 * Store used when the default one is not shared.
	 */
	public const FALLBACK_STORE = 'database';

	public static function isPerContainer(?string $driver): bool
	{
		return in_array($driver, self::PER_CONTAINER_DRIVERS, true);
	}

	/**
	 * @param string      $queue         the default queue connection
	 * @param string      $default_store the default cache store name
	 * @param string|null $driver        the driver of that store
	 */
	public static function storeName(string $queue, string $default_store, ?string $driver): string
	{
		return $queue !== 'sync' && self::isPerContainer($driver) ? self::FALLBACK_STORE : $default_store;
	}

	public function repository(): Repository
	{
		/** @var string $default_store */
		$default_store = config('cache.default');
		/** @var string|null $driver */
		$driver = config('cache.stores.' . $default_store . '.driver');
		/** @var string $queue */
		$queue = config('queue.default');

		return Cache::store(self::storeName($queue, $default_store, $driver));
	}
}
