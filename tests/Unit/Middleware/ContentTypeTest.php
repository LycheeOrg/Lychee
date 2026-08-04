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

use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\UnexpectedContentType;
use App\Http\Middleware\ContentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class ContentTypeTest extends AbstractTestCase
{
	public function testSkippedWhenFeatureDisabled(): void
	{
		Config::set('features.require-content-type', false);

		$request = $this->mock(Request::class);
		$middleware = new ContentType();

		self::assertEquals(1, $middleware->handle($request, fn () => 1, ContentType::JSON));
	}

	public function testJsonAccepted(): void
	{
		Config::set('features.require-content-type', true);

		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('isJson')->once()->andReturn(true);
		});
		$middleware = new ContentType();

		self::assertEquals(1, $middleware->handle($request, fn () => 1, ContentType::JSON));
	}

	public function testJsonRejected(): void
	{
		Config::set('features.require-content-type', true);

		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('isJson')->once()->andReturn(false);
		});
		$middleware = new ContentType();

		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, ContentType::JSON), UnexpectedContentType::class);
	}

	public function testMultipartAccepted(): void
	{
		Config::set('features.require-content-type', true);

		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('getContentTypeFormat')->once()->andReturn('form');
		});
		$middleware = new ContentType();

		self::assertEquals(1, $middleware->handle($request, fn () => 1, ContentType::MULTIPART));
	}

	public function testMultipartRejected(): void
	{
		Config::set('features.require-content-type', true);

		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('getContentTypeFormat')->once()->andReturn('json');
		});
		$middleware = new ContentType();

		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, ContentType::MULTIPART), UnexpectedContentType::class);
	}

	public function testInvalidContentType(): void
	{
		Config::set('features.require-content-type', true);

		$request = $this->mock(Request::class);
		$middleware = new ContentType();

		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, 'bogus'), LycheeInvalidArgumentException::class);
	}
}
