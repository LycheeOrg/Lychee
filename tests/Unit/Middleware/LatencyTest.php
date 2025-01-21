<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

use App\Http\Middleware\Latency;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\AbstractTestCase;

class LatencyTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testLatencyRequired(): void
	{
		config(['features.latency' => 1000]);
		$request = $this->mock(Request::class);
		$middleware = new Latency();

		$start = microtime(true);
		$middleware->handle($request, fn () => 1);
		$end = microtime(true);
		self::assertGreaterThan(1, $end - $start);
	}

	public function testNoLatency(): void
	{
		config(['features.latency' => 0]);
		$request = $this->mock(Request::class);
		$middleware = new Latency();

		$start = microtime(true);
		$middleware->handle($request, fn () => 1);
		$end = microtime(true);
		self::assertLessThan(1, $end - $start);
	}
}