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
use App\Exceptions\UnauthenticatedException;
use App\Http\Middleware\LoginRequired;
use App\Models\Configs;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\AbstractTestCase;

class LoginRequiredTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testExceptionLoginRequired(): void
	{
		Configs::where('key', 'login_required')->update(['value' => '1']);
		Configs::invalidateCache();
		$request = $this->mock(Request::class);
		$middleware = new LoginRequired();
		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, 'root'), UnauthenticatedException::class);
	}

	public function testExceptionLoginNotRequired(): void
	{
		Configs::where('key', 'login_required')->update(['value' => '1']);
		Configs::where('key', 'login_required_root_only')->update(['value' => '1']);
		Configs::invalidateCache();
		$request = $this->mock(Request::class);
		$middleware = new LoginRequired();
		self::assertEquals(1, $middleware->handle($request, fn () => 1, 'album'));
	}

	public function testExceptionWrongParam(): void
	{
		Configs::where('key', 'login_required')->update(['value' => '1']);
		Configs::invalidateCache();
		$request = $this->mock(Request::class);

		$middleware = new LoginRequired();
		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, 'nope'), LycheeInvalidArgumentException::class);
	}
}