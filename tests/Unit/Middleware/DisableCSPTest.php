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

use App\Http\Middleware\DisableCSP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\AbstractTestCase;

class DisableCSPTest extends AbstractTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		Config::set('app.dir_url', '');
		Config::set('debugbar.enabled', false);
		Config::set('log-viewer.route_path', 'Logs');
		Config::set('secure-headers.csp.enable', true);
	}

	public function testDisablesCspWhenDebugbarEnabled(): void
	{
		Config::set('debugbar.enabled', true);
		File::shouldReceive('exists')->once()->with(public_path('hot'))->andReturn(false);

		$request = Request::create('/any-page');
		$middleware = new DisableCSP();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
		self::assertFalse(config('secure-headers.csp.enable'));
	}

	public function testDisablesCspOnApiDocsRoute(): void
	{
		File::shouldReceive('exists')->once()->with(public_path('hot'))->andReturn(false);
		$request = Request::create('/docs/api');
		$middleware = new DisableCSP();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
		self::assertFalse(config('secure-headers.csp.enable'));
	}

	public function testDisablesCspOnRequestDocsRoute(): void
	{
		File::shouldReceive('exists')->once()->with(public_path('hot'))->andReturn(false);
		$request = Request::create('/request-docs');
		$middleware = new DisableCSP();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
		self::assertFalse(config('secure-headers.csp.enable'));
	}

	public function testDoesNotDisableCspOnRegularRoute(): void
	{
		File::shouldReceive('exists')->once()->with(public_path('hot'))->andReturn(false);
		$request = Request::create('/some/other/route');
		$middleware = new DisableCSP();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
		self::assertTrue(config('secure-headers.csp.enable'));
	}

	public function testRelaxesCspOnLogViewerRoute(): void
	{
		File::shouldReceive('exists')->once()->with(public_path('hot'))->andReturn(false);
		$request = Request::create('/Logs');
		$middleware = new DisableCSP();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
		self::assertTrue(config('secure-headers.csp.script-src.unsafe-eval'));
		self::assertTrue(config('secure-headers.csp.script-src.unsafe-inline'));
		self::assertEquals([], config('secure-headers.csp.script-src.hashes.sha256'));
	}

	public function testDisablesCspWhenHotFileExists(): void
	{
		File::shouldReceive('exists')->once()->with(public_path('hot'))->andReturn(true);

		$request = Request::create('/any-page');
		$middleware = new DisableCSP();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
		self::assertFalse(config('secure-headers.csp.enable'));
	}
}
