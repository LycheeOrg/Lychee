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

use App\Http\Middleware\FixStatusCode;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\AbstractTestCase;

class FixStatusCodeTest extends AbstractTestCase
{
	public function testEmptyContentBecomesNoContent(): void
	{
		$request = $this->mock(Request::class);
		$middleware = new FixStatusCode();

		$response = $middleware->handle($request, fn () => new Response(''));

		self::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
	}

	public function testNonEmptyContentIsUntouched(): void
	{
		$request = $this->mock(Request::class);
		$middleware = new FixStatusCode();

		$response = $middleware->handle($request, fn () => new Response('some content', Response::HTTP_OK));

		self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
	}

	public function testBinaryFileResponseIsUntouched(): void
	{
		$request = $this->mock(Request::class);
		$middleware = new FixStatusCode();

		$response = $middleware->handle($request, fn () => new BinaryFileResponse(base_path('composer.json')));

		self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
	}

	public function testStreamedResponseIsUntouched(): void
	{
		$request = $this->mock(Request::class);
		$middleware = new FixStatusCode();

		$response = $middleware->handle($request, fn () => new StreamedResponse(function (): void {}, Response::HTTP_OK));

		self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
	}
}
