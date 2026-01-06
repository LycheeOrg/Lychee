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

namespace Tests\Unit\Listeners;

use App\Listeners\LogQueryTimeout;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

class LogQueryTimeoutTest extends AbstractTestCase
{
	public function testQueryBelowWarningThreshold(): void
	{
		Log::shouldReceive('error')->never();
		Log::shouldReceive('warning')->never();

		Config::set('octane.max_execution_time', 30);

		$listener = new LogQueryTimeout();
		$event = new QueryExecuted(
			'SELECT * FROM users',
			[],
			10000, // 10 seconds (below 70% of 30s = 21s)
			new \Illuminate\Database\Connection(new \PDO('sqlite::memory:'))
		);

		$listener->handle($event);
	}

	public function testQueryAtWarningThreshold(): void
	{
		Log::shouldReceive('error')->never();
		Log::shouldReceive('warning')->once()->with('⚠️ WARNING: Slow query detected', \Mockery::type('array'));

		Config::set('octane.max_execution_time', 30);

		$listener = new LogQueryTimeout();
		$event = new QueryExecuted(
			'SELECT * FROM large_table',
			['param1'],
			22000, // 22 seconds (above 70% of 30s = 21s, below 90% = 27s)
			new \Illuminate\Database\Connection(new \PDO('sqlite::memory:'))
		);

		$listener->handle($event);
	}

	public function testQueryAtCriticalThreshold(): void
	{
		Log::shouldReceive('warning')->never();
		Log::shouldReceive('error')->once()->with('🚨 CRITICAL: Query approaching timeout', \Mockery::type('array'));

		Config::set('octane.max_execution_time', 30);

		$listener = new LogQueryTimeout();
		$event = new QueryExecuted(
			'SELECT * FROM very_large_table',
			['param1', 'param2'],
			28000, // 28 seconds (above 90% of 30s = 27s)
			new \Illuminate\Database\Connection(new \PDO('sqlite::memory:'))
		);

		$listener->handle($event);
	}
}
