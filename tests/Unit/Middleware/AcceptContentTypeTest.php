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
use App\Http\Middleware\AcceptContentType;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class AcceptContentTypeTest extends AbstractTestCase
{
	public function testExceptArrayPassesThrough(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('fullUrlIs')->once()->with('feed')->andReturn(true);
		});
		$middleware = new AcceptContentType();

		self::assertEquals(1, $middleware->handle($request, fn () => 1, AcceptContentType::JSON));
	}

	public function testExceptArrayCheckedViaIs(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('fullUrlIs')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('is')->once()->with('feed')->andReturn(true);
		});
		$middleware = new AcceptContentType();

		self::assertEquals(1, $middleware->handle($request, fn () => 1, AcceptContentType::JSON));
	}

	public function testJsonAccepted(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('fullUrlIs')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('is')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('expectsJson')->once()->andReturn(true);
		});
		$middleware = new AcceptContentType();

		self::assertEquals(1, $middleware->handle($request, fn () => 1, AcceptContentType::JSON));
	}

	public function testJsonRejected(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('fullUrlIs')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('is')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('expectsJson')->once()->andReturn(false);
		});
		$middleware = new AcceptContentType();

		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, AcceptContentType::JSON), UnexpectedContentType::class);
	}

	public function testHtmlAccepted(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('fullUrlIs')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('is')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('acceptsHtml')->once()->andReturn(true);
		});
		$middleware = new AcceptContentType();

		self::assertEquals(1, $middleware->handle($request, fn () => 1, AcceptContentType::HTML));
	}

	public function testHtmlRejected(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('fullUrlIs')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('is')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('acceptsHtml')->once()->andReturn(false);
		});
		$middleware = new AcceptContentType();

		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, AcceptContentType::HTML), UnexpectedContentType::class);
	}

	public function testAnyAcceptedEmpty(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('fullUrlIs')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('is')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('getAcceptableContentTypes')->once()->andReturn([]);
		});
		$middleware = new AcceptContentType();

		self::assertEquals(1, $middleware->handle($request, fn () => 1, AcceptContentType::ANY));
	}

	public function testAnyAcceptedWildcard(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('fullUrlIs')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('is')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['*']);
		});
		$middleware = new AcceptContentType();

		self::assertEquals(1, $middleware->handle($request, fn () => 1, AcceptContentType::ANY));
	}

	public function testAnyAcceptedDoubleWildcard(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('fullUrlIs')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('is')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['*/*']);
		});
		$middleware = new AcceptContentType();

		self::assertEquals(1, $middleware->handle($request, fn () => 1, AcceptContentType::ANY));
	}

	public function testAnyRejected(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('fullUrlIs')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('is')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('getAcceptableContentTypes')->once()->andReturn(['text/html']);
		});
		$middleware = new AcceptContentType();

		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, AcceptContentType::ANY), UnexpectedContentType::class);
	}

	public function testInvalidContentType(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('fullUrlIs')->once()->with('feed')->andReturn(false);
			$mock->shouldReceive('is')->once()->with('feed')->andReturn(false);
		});
		$middleware = new AcceptContentType();

		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, 'bogus'), LycheeInvalidArgumentException::class);
	}
}
