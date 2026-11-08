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

namespace Tests\Unit\Console\Commands;

use App\Services\Auth\LoginThrottleService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\AbstractTestCase;
use Tests\Support\Cache\LocklessArrayStore;

/**
 * Feature 112 – `php artisan lychee:blocked-ips [--unban=<ip>]`.
 */
class BlockedIpsTest extends AbstractTestCase
{
	private const LOCKLESS_ERROR = "The login throttle uses the cache store 'lockless', which has no locks (for example apc): blocked IPs cannot be listed or unbanned from the console.";

	public function setUp(): void
	{
		parent::setUp();
		Cache::flush();
		Config::set('app.timezone', 'UTC');
	}

	public function tearDown(): void
	{
		Cache::flush();
		parent::tearDown();
	}

	private function block(string $ip): void
	{
		$throttle = new LoginThrottleService();
		for ($i = 0; $i < LoginThrottleService::MAX_ATTEMPTS; $i++) {
			$throttle->hit($ip);
		}
	}

	/**
	 * S-112-04 – blocked IPs as a table, latest block end first.
	 */
	public function testListsBlockedIpsLatestFirst(): void
	{
		$this->travelTo(Carbon::parse('2026-11-08 14:00:00', 'UTC'));
		$this->block('2001:db8::1');
		$this->travel(30)->minutes();
		$this->block('203.0.113.7');
		$this->travel(10)->minutes();

		$this->artisan('lychee:blocked-ips')
			->expectsTable(['IP', 'Attempts', 'Blocked until', 'Minutes left'], [
				['203.0.113.7', 10, '2026-11-08 15:30:00 UTC', 50],
				['2001:db8::1', 10, '2026-11-08 15:00:00 UTC', 20],
			])
			->assertExitCode(0);
	}

	/**
	 * FR-112-03 – minutes left are rounded up.
	 */
	public function testMinutesLeftAreRoundedUp(): void
	{
		$this->travelTo(Carbon::parse('2026-11-08 14:00:00', 'UTC'));
		$this->block('203.0.113.7');
		$this->travelTo(Carbon::parse('2026-11-08 14:59:30', 'UTC'));

		$this->artisan('lychee:blocked-ips')
			->expectsTable(['IP', 'Attempts', 'Blocked until', 'Minutes left'], [
				['203.0.113.7', 10, '2026-11-08 15:00:00 UTC', 1],
			])
			->assertExitCode(0);
	}

	/**
	 * S-112-05 – nobody blocked.
	 */
	public function testEmptyList(): void
	{
		$this->artisan('lychee:blocked-ips')
			->expectsOutput('No blocked IP.')
			->assertExitCode(0);
	}

	/**
	 * S-112-06 – an entry whose counter has expired is not listed.
	 */
	public function testExpiredBlockIsNotListed(): void
	{
		$this->travelTo(Carbon::parse('2026-11-08 14:00:00', 'UTC'));
		$this->block('198.51.100.1');
		$this->travel(30)->minutes();
		$this->block('203.0.113.7');
		$this->travelTo(Carbon::parse('2026-11-08 15:00:01', 'UTC'));

		$this->artisan('lychee:blocked-ips')
			->expectsTable(['IP', 'Attempts', 'Blocked until', 'Minutes left'], [
				['203.0.113.7', 10, '2026-11-08 15:30:00 UTC', 30],
			])
			->assertExitCode(0);
	}

	/**
	 * S-112-07 – unbanning a blocked IP lifts the block and empties the list.
	 */
	public function testUnbanBlockedIp(): void
	{
		$this->block('203.0.113.7');

		$this->artisan('lychee:blocked-ips', ['--unban' => '203.0.113.7'])
			->expectsOutput('Unbanned 203.0.113.7.')
			->assertExitCode(0);

		self::assertFalse((new LoginThrottleService())->isBlocked('203.0.113.7'));
		$this->artisan('lychee:blocked-ips')
			->expectsOutput('No blocked IP.')
			->assertExitCode(0);
	}

	/**
	 * S-112-08 – unbanning an IP that is not blocked.
	 */
	public function testUnbanIpThatIsNotBlocked(): void
	{
		$this->artisan('lychee:blocked-ips', ['--unban' => '198.51.100.4'])
			->expectsOutput('198.51.100.4 is not blocked.')
			->assertExitCode(0);
	}

	/**
	 * S-112-09 – a value that is not an IP address.
	 */
	public function testUnbanInvalidIp(): void
	{
		$this->block('203.0.113.7');

		$this->artisan('lychee:blocked-ips', ['--unban' => 'nope'])
			->expectsOutput('nope is not a valid IP address.')
			->assertExitCode(2);

		self::assertTrue((new LoginThrottleService())->isBlocked('203.0.113.7'));
	}

	/**
	 * S-112-09 – an empty value is not an IP address either.
	 */
	public function testUnbanEmptyValue(): void
	{
		$this->artisan('lychee:blocked-ips', ['--unban' => ''])
			->expectsOutput(' is not a valid IP address.')
			->assertExitCode(2);
	}

	/**
	 * S-112-10 – an expanded IPv6 address unbans the compressed one.
	 */
	public function testUnbanExpandedIpv6(): void
	{
		$this->block('2001:db8::1');

		$this->artisan('lychee:blocked-ips', ['--unban' => '2001:0DB8:0000:0000:0000:0000:0000:0001'])
			->expectsOutput('Unbanned 2001:db8::1.')
			->assertExitCode(0);
	}

	/**
	 * S-112-13 – a store without locks is refused, for both forms.
	 */
	public function testLocklessStoreIsRefused(): void
	{
		LocklessArrayStore::register();
		$this->block('203.0.113.7');

		$this->artisan('lychee:blocked-ips')
			->expectsOutput(self::LOCKLESS_ERROR)
			->assertExitCode(1);
		$this->artisan('lychee:blocked-ips', ['--unban' => '203.0.113.7'])
			->expectsOutput(self::LOCKLESS_ERROR)
			->assertExitCode(1);

		self::assertTrue((new LoginThrottleService())->isBlocked('203.0.113.7'));
	}
}
