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

use App\Http\Middleware\CacheControl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\AbstractTestCase;

class CacheControlTest extends AbstractTestCase
{
	public function testUsesDefaultAge(): void
	{
		$request = $this->mock(Request::class);
		$middleware = new CacheControl();

		$response = $middleware->handle($request, fn () => new Response('ok'));

		self::assertTrue($response->headers->hasCacheControlDirective('private'));
		self::assertSame('3600', $response->headers->getCacheControlDirective('max-age'));
	}

	public function testUsesGivenAge(): void
	{
		$request = $this->mock(Request::class);
		$middleware = new CacheControl();

		$response = $middleware->handle($request, fn () => new Response('ok'), '120');

		self::assertTrue($response->headers->hasCacheControlDirective('private'));
		self::assertSame('120', $response->headers->getCacheControlDirective('max-age'));
	}
}
