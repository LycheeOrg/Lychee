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

use App\Http\Middleware\ResolveConfigs;
use App\Repositories\ConfigManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class ResolveConfigsTest extends AbstractTestCase
{
	public function testPassesThroughWhenConfigsTableMissing(): void
	{
		Schema::shouldReceive('hasTable')->once()->with('configs')->andReturn(false);

		$request = $this->mock(Request::class);
		$middleware = new ResolveConfigs();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
	}

	public function testPassesThroughWhenSchemaCheckThrows(): void
	{
		Schema::shouldReceive('hasTable')->once()->with('configs')->andThrow(new \Exception('no db connection'));

		$request = $this->mock(Request::class);
		$middleware = new ResolveConfigs();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
	}

	public function testResolvesAndStoresConfigManager(): void
	{
		Schema::shouldReceive('hasTable')->once()->with('configs')->andReturn(true);

		$this->mock(ConfigManager::class, function (MockInterface $mock): void {
			$mock->shouldReceive('getValueAsBool')->with('watermark_enabled')->andReturn(false);
		});

		$request = Request::create('/');
		$middleware = new ResolveConfigs();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
		self::assertInstanceOf(ConfigManager::class, $request->attributes->get('configs'));
	}
}
