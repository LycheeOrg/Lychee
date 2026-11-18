<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Services\Cache;

use App\Services\Cache\SharedCacheStore;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\DatabaseStore;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;

/**
 * Feature 116, S-116-19: the cache store shared by the web and the worker containers (ADR-116-01).
 */
class SharedCacheStoreTest extends AbstractTestCase
{
	/**
	 * @return array<string,array{0:string,1:string,2:?string,3:string}>
	 */
	public static function storeNameProvider(): array
	{
		return [
			'sync on file' => ['sync', 'file', 'file', 'file'],
			'sync on array' => ['sync', 'array', 'array', 'array'],
			'async on file' => ['database', 'file', 'file', 'database'],
			'async on array' => ['redis', 'array', 'array', 'database'],
			'async on apc' => ['database', 'apc', 'apc', 'database'],
			'async on redis' => ['database', 'redis', 'redis', 'redis'],
			'async on database' => ['redis', 'database', 'database', 'database'],
			'async on a custom store name' => ['database', 'my_memcached', 'memcached', 'my_memcached'],
			'async on an unknown store' => ['database', 'missing', null, 'missing'],
		];
	}

	#[DataProvider('storeNameProvider')]
	public function testStoreName(string $queue, string $default_store, ?string $driver, string $expected): void
	{
		self::assertSame($expected, SharedCacheStore::storeName($queue, $default_store, $driver));
	}

	/**
	 * @return array<string,array{0:?string,1:bool}>
	 */
	public static function perContainerProvider(): array
	{
		return [
			'file' => ['file', true],
			'array' => ['array', true],
			'apc' => ['apc', true],
			'redis' => ['redis', false],
			'database' => ['database', false],
			'memcached' => ['memcached', false],
			'null' => [null, false],
		];
	}

	#[DataProvider('perContainerProvider')]
	public function testIsPerContainer(?string $driver, bool $expected): void
	{
		self::assertSame($expected, SharedCacheStore::isPerContainer($driver));
	}

	public function testRepositoryUsesDatabaseStoreForAsyncQueueOnArrayCache(): void
	{
		Config::set('queue.default', 'database');
		Config::set('cache.default', 'array');

		self::assertInstanceOf(DatabaseStore::class, (new SharedCacheStore())->repository()->getStore());
	}

	public function testRepositoryUsesDefaultStoreOnSyncQueue(): void
	{
		Config::set('queue.default', 'sync');
		Config::set('cache.default', 'array');

		self::assertInstanceOf(ArrayStore::class, (new SharedCacheStore())->repository()->getStore());
	}
}
