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

use App\Exceptions\AdminUserRequiredException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Http\Middleware\AdminUserStatus;
use App\Http\Middleware\Checks\HasAdminUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class HasAdminUserTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testExceptionAdminUnset(): void
	{
		$mock = $this->mock(HasAdminUser::class, function (MockInterface $mock) {
			$mock->shouldReceive('assert')->once()->andReturn(false);
		});
		$request = $this->mock(Request::class);

		$middleware = new AdminUserStatus($mock);
		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, 'set'), AdminUserRequiredException::class);
	}

	public function testExceptionWrongParam(): void
	{
		$mock = $this->mock(HasAdminUser::class);
		$request = $this->mock(Request::class);

		$middleware = new AdminUserStatus($mock);
		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, 'nope'), LycheeInvalidArgumentException::class);
	}
}