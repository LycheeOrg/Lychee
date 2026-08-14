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

use App\Exceptions\Internal\LycheeLogicException;
use App\Repositories\ConfigManager;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Support\Facades\Cache;
use Tests\AbstractTestCase;

class ManagedCacheServiceTest extends AbstractTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		config(['features.enable-caching' => true]);
	}

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	private function makeService(bool $enabled): ManagedCacheService
	{
		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')
			->with('managed_cache_enabled')
			->andReturn($enabled);

		return new ManagedCacheService($config_manager);
	}

	// ── remember() ──────────────────────────────────────────────

	public function testRememberCacheMissExecutesCallbackAndStoresValue(): void
	{
		$service = $this->makeService(enabled: true);
		$calls = 0;

		$result = $service->remember('mc-test:miss', ['tag:a'], function () use (&$calls) {
			$calls++;

			return 'computed-value';
		}, ttl: 60);

		self::assertSame('computed-value', $result);
		self::assertSame(1, $calls);
		self::assertSame('computed-value', Cache::get('mc-test:miss'));
	}

	public function testRememberCacheHitDoesNotInvokeCallback(): void
	{
		$service = $this->makeService(enabled: true);
		Cache::put('mc-test:hit', 'cached-value', 60);
		$calls = 0;

		$result = $service->remember('mc-test:hit', ['tag:a'], function () use (&$calls) {
			$calls++;

			return 'recomputed-value';
		}, ttl: 60);

		self::assertSame('cached-value', $result);
		self::assertSame(0, $calls);
	}

	public function testRememberWithDisabledConfigAlwaysInvokesCallbackAndSkipsCacheIO(): void
	{
		$service = $this->makeService(enabled: false);
		$calls = 0;
		$callback = function () use (&$calls) {
			$calls++;

			return 'value-' . $calls;
		};

		$first = $service->remember('mc-test:disabled', ['tag:a'], $callback, ttl: 60);
		$second = $service->remember('mc-test:disabled', ['tag:a'], $callback, ttl: 60);

		self::assertSame('value-1', $first);
		self::assertSame('value-2', $second);
		self::assertSame(2, $calls);
		self::assertNull(Cache::get('mc-test:disabled'));
		self::assertNull(Cache::get(ManagedCacheService::TAG . 'tag:a'));
	}

	public function testRememberWithEmptyTagsArrayStillCachesTheValue(): void
	{
		$service = $this->makeService(enabled: true);

		$result = $service->remember('mc-test:no-tags', [], fn () => 'untagged-value', ttl: 60);

		self::assertSame('untagged-value', $result);
		self::assertSame('untagged-value', Cache::get('mc-test:no-tags'));
	}

	public function testRememberRecordsTheKeyUnderEveryTagPassed(): void
	{
		$service = $this->makeService(enabled: true);

		$service->remember('mc-test:multi-tag', ['tag:x', 'tag:y'], fn () => 'value', ttl: 60);

		// Either tag independently evicts the same key.
		$service->forgetTag('tag:x');
		self::assertNull(Cache::get('mc-test:multi-tag'), 'evicting the first tag should evict the key');

		$service->remember('mc-test:multi-tag', ['tag:x', 'tag:y'], fn () => 'value-again', ttl: 60);
		$service->forgetTag('tag:y');
		self::assertNull(Cache::get('mc-test:multi-tag'), 'evicting the second tag should also evict the key');
	}

	// ── rememberIf() ────────────────────────────────────────────

	public function testRememberIfWithFalseConditionAlwaysInvokesCallbackAndSkipsCacheIO(): void
	{
		$service = $this->makeService(enabled: true);
		$calls = 0;
		$callback = function () use (&$calls) {
			$calls++;

			return 'value-' . $calls;
		};

		$first = $service->rememberIf(false, 'mc-test:rememberif-false', ['tag:a'], $callback, ttl: 60);
		$second = $service->rememberIf(false, 'mc-test:rememberif-false', ['tag:a'], $callback, ttl: 60);

		self::assertSame('value-1', $first);
		self::assertSame('value-2', $second);
		self::assertSame(2, $calls);
		self::assertNull(Cache::get('mc-test:rememberif-false'));
	}

	public function testRememberIfWithTrueConditionDelegatesToRemember(): void
	{
		$service = $this->makeService(enabled: true);
		$calls = 0;

		$first = $service->rememberIf(true, 'mc-test:rememberif-true', ['tag:a'], function () use (&$calls) {
			$calls++;

			return 'computed-' . $calls;
		}, ttl: 60);
		$second = $service->rememberIf(true, 'mc-test:rememberif-true', ['tag:a'], function () use (&$calls) {
			$calls++;

			return 'computed-' . $calls;
		}, ttl: 60);

		self::assertSame('computed-1', $first);
		self::assertSame('computed-1', $second, 'second call should be a cache hit, not recomputed');
		self::assertSame(1, $calls);
	}

	// ── addTags() ───────────────────────────────────────────────

	public function testAddTagsAssociatesAdditionalTagsWithAnAlreadyCachedKey(): void
	{
		$service = $this->makeService(enabled: true);
		$service->remember('mc-test:add-tags', ['tag:known'], fn () => 'value', ttl: 60);

		$service->addTags('mc-test:add-tags', ['tag:late']);

		$service->forgetTag('tag:late');
		self::assertNull(Cache::get('mc-test:add-tags'));
	}

	public function testAddTagsIsNoOpWhenKeyIsNotCurrentlyCached(): void
	{
		$service = $this->makeService(enabled: true);

		$service->addTags('mc-test:never-cached', ['tag:orphan']);

		// No key was ever recorded under the tag, so evicting it must be safe and inert.
		$service->forgetTag('tag:orphan');
		self::assertNull(Cache::get(ManagedCacheService::TAG . 'tag:orphan'));
	}

	public function testAddTagsIsNoOpWhenManagedCacheIsDisabled(): void
	{
		$service = $this->makeService(enabled: false);

		$service->addTags('mc-test:disabled-add-tags', ['tag:ignored']);

		self::assertNull(Cache::get(ManagedCacheService::TAG . 'tag:ignored'));
	}

	// ── forgetTag() ─────────────────────────────────────────────

	public function testForgetTagEvictsEveryMemberKeyAndTheTagItself(): void
	{
		$service = $this->makeService(enabled: true);
		$service->remember('mc-test:shared-1', ['tag:shared'], fn () => 'value-1', ttl: 60);
		$service->remember('mc-test:shared-2', ['tag:shared'], fn () => 'value-2', ttl: 60);

		$service->forgetTag('tag:shared');

		self::assertNull(Cache::get('mc-test:shared-1'));
		self::assertNull(Cache::get('mc-test:shared-2'));
		self::assertNull(Cache::get(ManagedCacheService::TAG . 'tag:shared'));
	}

	public function testForgetTagOnAnUnknownTagIsANoOp(): void
	{
		$service = $this->makeService(enabled: true);

		$service->forgetTag('tag:never-used');

		self::assertNull(Cache::get(ManagedCacheService::TAG . 'tag:never-used'));
	}

	public function testForgetTagThrowsWhenAMemberKeyIsNotAString(): void
	{
		$service = $this->makeService(enabled: true);
		// A purely-numeric key is coerced to an int array key by PHP, so the
		// stored member set no longer satisfies `is_string($key)`.
		$service->remember('12345', ['tag:numeric-key'], fn () => 'value', ttl: 60);

		$this->expectException(LycheeLogicException::class);

		$service->forgetTag('tag:numeric-key');
	}
}
