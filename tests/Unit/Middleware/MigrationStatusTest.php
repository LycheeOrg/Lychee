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

use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\MigrationAlreadyCompletedException;
use App\Exceptions\MigrationRequiredException;
use App\Http\Middleware\Checks\IsMigrated;
use App\Http\Middleware\MigrationStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class MigrationStatusTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testExceptionMigrationComplete(): void
	{
		$mock = $this->mock(IsMigrated::class, function (MockInterface $mock) {
			$mock->shouldReceive('assert')->once()->andReturn(false);
		});
		$request = $this->mock(Request::class);

		$middleware = new MigrationStatus($mock);
		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, 'complete'), MigrationRequiredException::class);
	}

	public function testExceptionMigrationIncomplete(): void
	{
		$mock = $this->mock(IsMigrated::class, function (MockInterface $mock) {
			$mock->shouldReceive('assert')->once()->andReturn(true);
		});
		$request = $this->mock(Request::class);

		$middleware = new MigrationStatus($mock);
		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, 'incomplete'), MigrationAlreadyCompletedException::class);

		$mock = $this->mock(IsMigrated::class, function (MockInterface $mock) {
			$mock->shouldReceive('assert')->once()->andReturn(false);
		});
		$request = $this->mock(Request::class);

		$middleware = new MigrationStatus($mock);
		self::assertEquals(1, $middleware->handle($request, fn () => 1, 'incomplete'), MigrationAlreadyCompletedException::class);
	}

	public function testExceptionWrongParam(): void
	{
		$mock = $this->mock(IsMigrated::class);
		$request = $this->mock(Request::class);

		$middleware = new MigrationStatus($mock);
		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, 'nope'), LycheeInvalidArgumentException::class);
	}
}