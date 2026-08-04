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

use App\Http\Middleware\SetLocale;
use App\Repositories\ConfigManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class SetLocaleTest extends AbstractTestCase
{
	public function testSetsLocaleFromConfigManager(): void
	{
		$this->mock(ConfigManager::class, function (MockInterface $mock): void {
			$mock->shouldReceive('getValueAsString')->once()->with('lang')->andReturn('fr');
		});

		$request = $this->mock(Request::class);
		$middleware = new SetLocale();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
		self::assertEquals('fr', app()->getLocale());
	}

	public function testFallsBackToAppLocaleOnFailure(): void
	{
		Config::set('app.locale', 'de');

		$this->mock(ConfigManager::class, function (MockInterface $mock): void {
			$mock->shouldReceive('getValueAsString')->once()->with('lang')->andThrow(new \Exception('boom'));
		});

		$request = $this->mock(Request::class);
		$middleware = new SetLocale();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
		self::assertEquals('de', app()->getLocale());
	}
}
