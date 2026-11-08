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

namespace Tests\Unit\Services\Auth;

use App\Services\Auth\LoginThrottleService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;
use Tests\Support\Cache\LocklessArrayStore;

/**
 * Feature 112 – login throttle and its blocked-IP index.
 */
class LoginThrottleServiceTest extends AbstractTestCase
{
	private const IP = '203.0.113.7';

	public function setUp(): void
	{
		parent::setUp();
		Cache::flush();
	}

	public function tearDown(): void
	{
		Cache::flush();
		parent::tearDown();
	}

	private function hitTimes(LoginThrottleService $throttle, string $ip, int $times): void
	{
		for ($i = 0; $i < $times; $i++) {
			$throttle->hit($ip);
		}
	}

	/**
	 * S-112-10 – one canonical form per address, null for anything else.
	 */
	public function testNormalizeIp(): void
	{
		self::assertSame('203.0.113.7', LoginThrottleService::normalizeIp('203.0.113.7'));
		self::assertSame('2001:db8::1', LoginThrottleService::normalizeIp('2001:0db8:0000:0000:0000:0000:0000:0001'));
		self::assertSame('2001:db8::1', LoginThrottleService::normalizeIp('2001:DB8::1'));
		self::assertNull(LoginThrottleService::normalizeIp('nope'));
		self::assertNull(LoginThrottleService::normalizeIp(''));
		self::assertNull(LoginThrottleService::normalizeIp('203.0.113.7/24'));
	}

	/**
	 * S-112-11 – ended blocks are dropped.
	 */
	public function testPruneDropsEndedBlocks(): void
	{
		self::assertSame(['a' => 200], LoginThrottleService::prune(['a' => 200, 'b' => 100, 'c' => 50], 100));
	}

	/**
	 * S-112-11 – the latest block ends are kept.
	 */
	public function testCapKeepsTheLatestBlockEnds(): void
	{
		self::assertSame(['c' => 300, 'a' => 200], LoginThrottleService::cap(['a' => 200, 'b' => 100, 'c' => 300], 2));
		self::assertSame(['a' => 200], LoginThrottleService::cap(['a' => 200], 2));
	}

	/**
	 * S-112-02 – the 10th attempt indexes the IP, attempts 1–9 and 11 do not.
	 */
	public function testIndexIsWrittenOnlyAtTheLimit(): void
	{
		$this->freezeTime();
		$throttle = new LoginThrottleService();

		$this->hitTimes($throttle, self::IP, 9);
		self::assertFalse($throttle->isBlocked(self::IP));
		self::assertNull(Cache::get(LoginThrottleService::INDEX_KEY));

		self::assertSame(10, $throttle->hit(self::IP));
		self::assertTrue($throttle->isBlocked(self::IP));
		self::assertSame([self::IP => Carbon::now()->getTimestamp() + 3600], Cache::get(LoginThrottleService::INDEX_KEY));

		$blocked = $throttle->blocked();
		self::assertCount(1, $blocked);
		self::assertSame(self::IP, $blocked[0]->ip);
		self::assertSame(10, $blocked[0]->attempts);
		self::assertSame(Carbon::now()->getTimestamp() + 3600, $blocked[0]->blocked_until);

		Cache::forget(LoginThrottleService::INDEX_KEY);
		$throttle->hit(self::IP);
		self::assertNull(Cache::get(LoginThrottleService::INDEX_KEY));
	}

	/**
	 * S-112-11 – writing an entry drops the blocks that have ended.
	 */
	public function testIndexWriteDropsEndedBlocks(): void
	{
		$this->freezeTime();
		Cache::put(LoginThrottleService::INDEX_KEY, ['198.51.100.1' => Carbon::now()->getTimestamp() - 1], 3600);
		$throttle = new LoginThrottleService();

		$this->hitTimes($throttle, self::IP, 10);

		self::assertSame([self::IP => Carbon::now()->getTimestamp() + 3600], Cache::get(LoginThrottleService::INDEX_KEY));
	}

	/**
	 * An address that is not an IP is throttled but never indexed.
	 */
	public function testInvalidIpIsThrottledButNotIndexed(): void
	{
		$throttle = new LoginThrottleService();

		$this->hitTimes($throttle, '', 10);

		self::assertTrue($throttle->isBlocked(''));
		self::assertNull(Cache::get(LoginThrottleService::INDEX_KEY));
	}

	/**
	 * S-112-12 – a held lock leaves the IP out of the list and logs a warning.
	 */
	public function testHeldLockSkipsTheIndexAndLogs(): void
	{
		$throttle = new LoginThrottleService();
		$lock = Cache::lock(LoginThrottleService::LOCK_KEY, 10);
		self::assertTrue($lock->get());

		Log::shouldReceive('channel')->with('login')->andReturnSelf();
		Log::shouldReceive('warning')->once()->with('Blocked-IP list not updated for ' . self::IP . ': lock timeout');
		Log::shouldReceive('notice')->never();

		$this->hitTimes($throttle, self::IP, 10);
		$lock->release();

		self::assertTrue($throttle->isBlocked(self::IP));
		self::assertNull(Cache::get(LoginThrottleService::INDEX_KEY));
	}

	/**
	 * TE-112-01 – the block is logged once, with the IP only.
	 */
	public function testBlockIsLogged(): void
	{
		$this->freezeTime();
		$throttle = new LoginThrottleService();
		$until = Carbon::now()->addSeconds(3600)->toIso8601String();

		Log::shouldReceive('channel')->with('login')->andReturnSelf();
		Log::shouldReceive('notice')->once()->with('IP ' . self::IP . ' blocked from login until ' . $until);

		$this->hitTimes($throttle, self::IP, 11);
	}

	/**
	 * S-112-13 – a store without locks still throttles and writes no index.
	 */
	public function testLocklessStoreThrottlesWithoutIndex(): void
	{
		LocklessArrayStore::register();
		$throttle = new LoginThrottleService();

		self::assertFalse($throttle->hasLocks());
		self::assertSame('lockless', $throttle->storeName());

		$this->hitTimes($throttle, self::IP, 10);

		self::assertTrue($throttle->isBlocked(self::IP));
		self::assertNull(Cache::store('lockless')->get(LoginThrottleService::INDEX_KEY));
	}

	/**
	 * The default store is named after `cache.default` when `cache.limiter` is unset.
	 */
	public function testStoreNameFallsBackToTheDefaultStore(): void
	{
		$throttle = new LoginThrottleService();

		self::assertTrue($throttle->hasLocks());
		self::assertSame('array', $throttle->storeName());
	}

	/**
	 * S-112-07 – unbanning a blocked IP clears its counter and its entry.
	 */
	public function testUnbanClearsTheBlock(): void
	{
		$throttle = new LoginThrottleService();
		$this->hitTimes($throttle, self::IP, 10);

		self::assertTrue($throttle->unban(self::IP));

		self::assertFalse($throttle->isBlocked(self::IP));
		self::assertSame([], $throttle->blocked());
		self::assertSame([], Cache::get(LoginThrottleService::INDEX_KEY));
		self::assertFalse($throttle->unban(self::IP));
	}

	/**
	 * S-112-08 – unbanning an IP that is not blocked still resets its counter.
	 */
	public function testUnbanResetsAnUnblockedCounter(): void
	{
		$throttle = new LoginThrottleService();
		$this->hitTimes($throttle, self::IP, 4);

		self::assertFalse($throttle->unban(self::IP));

		self::assertSame(1, $throttle->hit(self::IP));
	}

	/**
	 * S-112-10 – every IPv6 spelling shares one counter.
	 */
	public function testIpv6FormsShareOneCounter(): void
	{
		$throttle = new LoginThrottleService();
		$this->hitTimes($throttle, '2001:db8::1', 10);

		self::assertTrue($throttle->isBlocked('2001:0db8:0:0:0:0:0:1'));
		self::assertTrue($throttle->unban('2001:0DB8::0001'));
		self::assertFalse($throttle->isBlocked('2001:db8::1'));
	}

	/**
	 * S-112-06 – an entry whose counter has expired is not listed; newest block end first.
	 */
	public function testBlockedSkipsExpiredCountersAndSortsNewestFirst(): void
	{
		$this->travelTo(Carbon::parse('2026-11-08 14:00:00', 'UTC'));
		$throttle = new LoginThrottleService();
		$this->hitTimes($throttle, '198.51.100.1', 10);
		$this->travel(20)->minutes();
		$this->hitTimes($throttle, '2001:db8::1', 10);
		$this->travel(10)->minutes();
		$this->hitTimes($throttle, self::IP, 10);

		self::assertSame([self::IP, '2001:db8::1', '198.51.100.1'], array_map(fn ($b) => $b->ip, $throttle->blocked()));

		$this->travelTo(Carbon::parse('2026-11-08 15:00:01', 'UTC'));
		self::assertSame([self::IP, '2001:db8::1'], array_map(fn ($b) => $b->ip, $throttle->blocked()));
	}
}
