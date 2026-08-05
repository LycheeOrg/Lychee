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

use App\Http\Middleware\ConfigIntegrity;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\AbstractTestCase;

/**
 * Unit test for the {@see ConfigIntegrity} middleware itself (the SE_FIELDS /
 * PRO_FIELDS content is exercised against a real database in ConfigIntegrityTest).
 */
class ConfigIntegrityMiddlewareTest extends AbstractTestCase
{
	public function testUpdatesLevelsOnBothFieldSets(): void
	{
		$builder = \Mockery::mock(Builder::class);
		$builder->shouldReceive('whereIn')->once()->with('key', ConfigIntegrity::SE_FIELDS)->andReturnSelf();
		$builder->shouldReceive('whereIn')->once()->with('key', ConfigIntegrity::PRO_FIELDS)->andReturnSelf();
		$builder->shouldReceive('update')->once()->with(['level' => 1])->andReturn(0);
		$builder->shouldReceive('update')->once()->with(['level' => 2])->andReturn(0);

		DB::shouldReceive('table')->twice()->with('configs')->andReturn($builder);

		$request = $this->mock(Request::class);
		$middleware = new ConfigIntegrity();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
	}

	public function testFailsSilentlyWhenDatabaseIsNotReady(): void
	{
		DB::shouldReceive('table')->once()->with('configs')->andThrow(new \Exception('not installed yet'));

		$request = $this->mock(Request::class);
		$middleware = new ConfigIntegrity();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
	}
}
