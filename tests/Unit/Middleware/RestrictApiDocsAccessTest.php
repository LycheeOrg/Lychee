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

use App\Http\Middleware\RestrictApiDocsAccess;
use Dedoc\Scramble\Scramble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\AbstractTestCase;

class RestrictApiDocsAccessTest extends AbstractTestCase
{
	public function testAbortsWhenWhiteLabelEnabled(): void
	{
		Config::set('features.white_label_enabled', true);

		$request = $this->mock(Request::class);
		$middleware = new RestrictApiDocsAccess();

		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1), NotFoundHttpException::class);
	}

	public function testSkipsCacheWarmingWhenStoreNotConfigured(): void
	{
		Config::set('features.white_label_enabled', false);
		Config::set('scramble.cache.store', null);
		Config::set('scramble.cache.key', 'scramble.openapi');

		$request = $this->mock(Request::class);
		$middleware = new RestrictApiDocsAccess();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
	}

	public function testSkipsCacheWarmingWhenKeyNotConfigured(): void
	{
		Config::set('features.white_label_enabled', false);
		Config::set('scramble.cache.store', 'array');
		Config::set('scramble.cache.key', null);

		$request = $this->mock(Request::class);
		$middleware = new RestrictApiDocsAccess();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
	}

	public function testSkipsGenerationWhenAlreadyCached(): void
	{
		Config::set('features.white_label_enabled', false);
		Config::set('scramble.cache.store', 'array');
		Config::set('scramble.cache.key', 'scramble.openapi.test');

		Cache::store('array')->forever('scramble.openapi.test:' . Scramble::DEFAULT_API, ['cached' => true]);

		$request = $this->mock(Request::class);
		$middleware = new RestrictApiDocsAccess();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
	}
}
