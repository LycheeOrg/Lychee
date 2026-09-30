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

use App\Http\Middleware\ResolveVerify;
use App\Repositories\ConfigManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use LycheeVerify\Verify;
use Tests\AbstractTestCase;

class ResolveVerifyTest extends AbstractTestCase
{
	/**
	 * {@see \App\Http\Middleware\ResolveConfigs} did not set `configs`
	 * (not installed yet): pass through without resolving the license.
	 */
	public function testPassesThroughWhenConfigsWereNotResolved(): void
	{
		Schema::shouldReceive('hasTable')->never();

		$request = Request::create('/');
		$middleware = new ResolveVerify();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
		self::assertFalse($request->attributes->has('verify'));
	}

	public function testResolvesAndStoresVerify(): void
	{
		Schema::shouldReceive('hasTable')->never();
		$this->mock(Verify::class);

		$request = Request::create('/');
		$request->attributes->set('configs', $this->mock(ConfigManager::class));
		$middleware = new ResolveVerify();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
		self::assertInstanceOf(Verify::class, $request->attributes->get('verify'));
	}
}
