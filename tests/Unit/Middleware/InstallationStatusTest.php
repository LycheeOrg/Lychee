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

use App\Exceptions\InstallationAlreadyCompletedException;
use App\Exceptions\InstallationRequiredException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Http\Middleware\Checks\IsInstalled;
use App\Http\Middleware\InstallationStatus;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class InstallationStatusTest extends AbstractTestCase
{
	public function testCompleteAndInstalled(): void
	{
		$mock = $this->mock(IsInstalled::class, function (MockInterface $mock): void {
			$mock->shouldReceive('assert')->once()->andReturn(true);
		});
		$request = $this->mock(Request::class);

		$middleware = new InstallationStatus($mock);
		self::assertEquals(1, $middleware->handle($request, fn () => 1, 'complete'));
	}

	public function testCompleteButNotInstalled(): void
	{
		$mock = $this->mock(IsInstalled::class, function (MockInterface $mock): void {
			$mock->shouldReceive('assert')->once()->andReturn(false);
		});
		$request = $this->mock(Request::class);

		$middleware = new InstallationStatus($mock);
		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, 'complete'), InstallationRequiredException::class);
	}

	public function testIncompleteAndNotInstalled(): void
	{
		$mock = $this->mock(IsInstalled::class, function (MockInterface $mock): void {
			$mock->shouldReceive('assert')->once()->andReturn(false);
		});
		$request = $this->mock(Request::class);

		$middleware = new InstallationStatus($mock);
		self::assertEquals(1, $middleware->handle($request, fn () => 1, 'incomplete'));
	}

	public function testIncompleteButInstalled(): void
	{
		$mock = $this->mock(IsInstalled::class, function (MockInterface $mock): void {
			$mock->shouldReceive('assert')->once()->andReturn(true);
		});
		$request = $this->mock(Request::class);

		$middleware = new InstallationStatus($mock);
		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, 'incomplete'), InstallationAlreadyCompletedException::class);
	}

	public function testExceptionWrongParam(): void
	{
		$mock = $this->mock(IsInstalled::class);
		$request = $this->mock(Request::class);

		$middleware = new InstallationStatus($mock);
		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, 'nope'), LycheeInvalidArgumentException::class);
	}
}
