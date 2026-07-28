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

use App\Models\Configs;
use App\Repositories\ConfigManager;
use App\Services\Cache\ManagedCacheService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\AbstractTestCase;

class ManagedCacheServiceTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private ManagedCacheService $service;

	public function setUp(): void
	{
		parent::setUp();
		Configs::set('managed_cache_enabled', '1');
		$this->service = new ManagedCacheService(new ConfigManager());
	}

	public function testRememberCacheMissExecutesCallbackAndStoresValue(): void
	{
		$calls = 0;
		$value = $this->service->remember('mc-test:key1', ['tag1'], 60, function () use (&$calls) {
			$calls++;

			return 'computed-value';
		});

		self::assertSame('computed-value', $value);
		self::assertSame(1, $calls);
		self::assertSame('computed-value', Cache::get('mc-test:key1'));
	}

	public function testRememberCacheHitDoesNotReinvokeCallback(): void
	{
		$calls = 0;
		$callback = function () use (&$calls) {
			$calls++;

			return 'computed-value';
		};

		$first = $this->service->remember('mc-test:key2', ['tag1'], 60, $callback);
		$second = $this->service->remember('mc-test:key2', ['tag1'], 60, $callback);

		self::assertSame('computed-value', $first);
		self::assertSame('computed-value', $second);
		self::assertSame(1, $calls);
	}

	public function testForgetTagEvictsAllMemberKeysAndTheTagItself(): void
	{
		$this->service->remember('mc-test:key3', ['shared-tag'], 60, fn () => 'value3');
		$this->service->remember('mc-test:key4', ['shared-tag'], 60, fn () => 'value4');

		self::assertNotNull(Cache::get('mc-test:key3'));
		self::assertNotNull(Cache::get('mc-test:key4'));

		$this->service->forgetTag('shared-tag');

		self::assertNull(Cache::get('mc-test:key3'));
		self::assertNull(Cache::get('mc-test:key4'));
		self::assertNull(Cache::get(ManagedCacheService::TAG . 'shared-tag'));
	}

	public function testForgetTagWithMultipleTagsOnOneKeyEvictsFromEachTag(): void
	{
		$this->service->remember('mc-test:key5', ['tag-a', 'tag-b'], 60, fn () => 'value5');

		$this->service->forgetTag('tag-a');
		self::assertNull(Cache::get('mc-test:key5'));

		// Re-cache and confirm the other tag also evicts it independently.
		$this->service->remember('mc-test:key5', ['tag-a', 'tag-b'], 60, fn () => 'value5-again');
		$this->service->forgetTag('tag-b');
		self::assertNull(Cache::get('mc-test:key5'));
	}

	public function testForgetTagOnUnknownTagIsNoOp(): void
	{
		// Must not throw.
		$this->service->forgetTag('never-used-tag');
		self::assertTrue(true);
	}

	public function testRememberFallsBackToCallbackValueOnCacheWriteFailure(): void
	{
		Cache::shouldReceive('get')->once()->with('mc-test:key6')->andReturn(null);
		Cache::shouldReceive('put')->once()->andThrow(new \Exception('cache store unavailable'));

		$value = $this->service->remember('mc-test:key6', ['tag1'], 60, fn () => 'fallback-value');

		self::assertSame('fallback-value', $value);
	}

	public function testAddTagsAssociatesAnExistingKeyWithAdditionalTags(): void
	{
		$this->service->remember('mc-test:key8', ['tag-main'], 60, fn () => 'value8');

		$this->service->addTags('mc-test:key8', ['tag-extra-1', 'tag-extra-2']);

		$this->service->forgetTag('tag-extra-1');
		self::assertNull(Cache::get('mc-test:key8'));

		$this->service->remember('mc-test:key8', ['tag-main'], 60, fn () => 'value8-again');
		$this->service->addTags('mc-test:key8', ['tag-extra-1', 'tag-extra-2']);
		$this->service->forgetTag('tag-extra-2');
		self::assertNull(Cache::get('mc-test:key8'));
	}

	public function testAddTagsIsNoOpWhenKeyIsNotCached(): void
	{
		// Must not throw, and must not create a dangling tag pointing at a never-cached key.
		$this->service->addTags('mc-test:never-cached-key', ['tag-never']);
		$this->service->forgetTag('tag-never');
		self::assertTrue(true);
	}

	/**
	 * S-052-10: an entry older than its TTL is treated as absent on the next
	 * remember() call and is recomputed — standard Cache::get() TTL semantics,
	 * no bespoke expiry logic in ManagedCacheService itself.
	 */
	public function testRememberRecomputesAfterTtlExpires(): void
	{
		$calls = 0;
		$callback = function () use (&$calls) {
			$calls++;

			return 'value-' . $calls;
		};

		Carbon::setTestNow(Carbon::parse('2026-01-01 00:00:00'));
		$first = $this->service->remember('mc-test:ttl-key', ['tag1'], 5, $callback);

		Carbon::setTestNow(Carbon::parse('2026-01-01 00:00:10')); // 10s later, TTL was 5s.
		$second = $this->service->remember('mc-test:ttl-key', ['tag1'], 5, $callback);

		Carbon::setTestNow();

		self::assertSame('value-1', $first);
		self::assertSame('value-2', $second);
		self::assertSame(2, $calls);
	}

	public function testRememberSkipsAllCacheIOWhenManagedCacheDisabled(): void
	{
		Configs::set('managed_cache_enabled', '0');
		$service = new ManagedCacheService(new ConfigManager());

		$calls = 0;
		$callback = function () use (&$calls) {
			$calls++;

			return 'computed-value';
		};

		$first = $service->remember('mc-test:key7', ['tag1'], 60, $callback);
		$second = $service->remember('mc-test:key7', ['tag1'], 60, $callback);

		self::assertSame('computed-value', $first);
		self::assertSame('computed-value', $second);
		self::assertSame(2, $calls, 'callback should be invoked every time when the service is disabled');
		self::assertNull(Cache::get('mc-test:key7'));
	}
}
