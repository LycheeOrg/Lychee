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

namespace Tests\Unit\Middleware;

use App\Http\Middleware\ThrottleLogin;
use App\Repositories\ConfigManager;
use Illuminate\Cache\Events\WritingKey;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\AbstractTestCase;

/**
 * Feature 112 – `login_throttle` middleware on `POST /api/v2/Auth::login`.
 */
class ThrottleLoginTest extends AbstractTestCase
{
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

	private function request(): Request
	{
		return Request::create('/api/v2/Auth::login', 'POST', server: ['REMOTE_ADDR' => '203.0.113.7']);
	}

	private function pass(ThrottleLogin $middleware): Response
	{
		return $middleware->handle($this->request(), fn () => new Response('ok'));
	}

	/**
	 * FR-112-01 – attempts 1–10 reach the next handler and carry the rate-limit headers.
	 */
	public function testAttemptsUnderTheLimitPassWithHeaders(): void
	{
		$middleware = resolve(ThrottleLogin::class);

		for ($i = 1; $i <= 10; $i++) {
			$response = $this->pass($middleware);
			self::assertSame('ok', $response->getContent());
			self::assertSame('10', $response->headers->get('X-RateLimit-Limit'));
			self::assertSame(strval(10 - $i), $response->headers->get('X-RateLimit-Remaining'));
		}
	}

	/**
	 * S-112-01 – the 11th attempt is rejected with the retry headers and never reaches the handler.
	 */
	public function testEleventhAttemptIsRejectedWithRetryHeaders(): void
	{
		$this->freezeTime();
		$middleware = resolve(ThrottleLogin::class);
		for ($i = 1; $i <= 10; $i++) {
			$this->pass($middleware);
		}

		$called = false;
		try {
			$middleware->handle($this->request(), function () use (&$called) {
				$called = true;

				return new Response('ok');
			});
			self::fail('The 11th attempt was not throttled.');
		} catch (ThrottleRequestsException $e) {
			self::assertSame(429, $e->getStatusCode());
			self::assertSame([
				'X-RateLimit-Limit' => 10,
				'X-RateLimit-Remaining' => 0,
				'Retry-After' => 3600,
				'X-RateLimit-Reset' => Carbon::now()->getTimestamp() + 3600,
			], $e->getHeaders());
		}
		self::assertFalse($called);
	}

	/**
	 * S-112-03 / NFR-112-01 – a blocked request runs no query and writes nothing to the cache.
	 */
	public function testBlockedRequestRunsNoQueryAndWritesNoCache(): void
	{
		$middleware = resolve(ThrottleLogin::class);
		$writes = 0;
		Event::listen(WritingKey::class, function () use (&$writes) {
			$writes++;
		});
		// `ResolveConfigs` loads the configs before any route middleware runs; `CacheListener`
		// (every cache event) reads `cache_event_logging` from them. Load them here the same way.
		app(ConfigManager::class)->load();
		DB::enableQueryLog();

		DB::flushQueryLog();
		for ($i = 1; $i <= 10; $i++) {
			$this->pass($middleware);
		}
		self::assertSame([], DB::getQueryLog());
		self::assertGreaterThan(0, $writes, 'Counting attempts writes to the cache: the listener must see it.');

		$writes = 0;
		try {
			$this->pass($middleware);
			self::fail('The 11th attempt was not throttled.');
		} catch (ThrottleRequestsException) {
			// expected
		}

		self::assertSame(0, $writes);
		self::assertSame([], DB::getQueryLog());
		DB::disableQueryLog();
	}
}
