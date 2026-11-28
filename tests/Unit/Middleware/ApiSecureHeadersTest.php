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

use App\Http\Middleware\ApiSecureHeaders;
use Bepsvpt\SecureHeaders\SecureHeaders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Tests\AbstractTestCase;

class ApiSecureHeadersTest extends AbstractTestCase
{
	private const MINIMAL_CSP = "default-src 'none'; frame-ancestors 'none'";

	protected function setUp(): void
	{
		parent::setUp();
		Config::set('secure-headers.csp.frame-ancestors', ['none' => true, 'allow' => ['']]);
	}

	public function testJsonResponseGetsMinimalHeaders(): void
	{
		$headers = $this->handle(new JsonResponse(['id' => 'abc']));

		self::assertSame(self::MINIMAL_CSP, $headers['Content-Security-Policy']);
		self::assertArrayNotHasKey('Permissions-Policy', $headers);
	}

	public function testNoContentResponseGetsMinimalHeaders(): void
	{
		$headers = $this->handle(new Response(null, 204));

		self::assertSame(self::MINIMAL_CSP, $headers['Content-Security-Policy']);
		self::assertArrayNotHasKey('Permissions-Policy', $headers);
	}

	public function testEmptyResponseOfAVoidControllerGetsMinimalHeaders(): void
	{
		// The router wraps a controller returning nothing as an empty text/html response:
		// FixStatusCode only turns it into a 204 after the security headers are set.
		$headers = $this->handle(new Response('', 200, ['Content-Type' => 'text/html; charset=UTF-8']));

		self::assertSame(self::MINIMAL_CSP, $headers['Content-Security-Policy']);
		self::assertArrayNotHasKey('Permissions-Policy', $headers);
	}

	public function testDownloadGetsMinimalHeaders(): void
	{
		$headers = $this->handle(new Response('PK', 200, [
			'Content-Type' => 'application/zip',
			'Content-Disposition' => 'attachment; filename="album.zip"',
		]));

		self::assertSame(self::MINIMAL_CSP, $headers['Content-Security-Policy']);
		self::assertArrayNotHasKey('Permissions-Policy', $headers);
	}

	public function testHtmlResponseKeepsThePageHeaders(): void
	{
		// An API URL opened in a browser tab renders its error as an HTML page.
		$page_csp = config('secure-headers.csp');

		$headers = $this->handle(new Response('<!DOCTYPE html>', 404, ['Content-Type' => 'text/html; charset=UTF-8']));

		self::assertSame($page_csp, config('secure-headers.csp'));
		self::assertArrayHasKey('Permissions-Policy', $headers);
	}

	public function testConfiguredFrameAncestorsAreKept(): void
	{
		Config::set('secure-headers.csp.frame-ancestors', ['none' => false, 'allow' => ['https://blog.example.com']]);

		$headers = $this->handle(new JsonResponse([]));

		self::assertSame("default-src 'none'; frame-ancestors https://blog.example.com", $headers['Content-Security-Policy']);
	}

	/**
	 * Runs the middleware, then builds the headers the way the global
	 * SecureHeadersMiddleware does once the route pipeline has returned.
	 *
	 * @return array<string,string>
	 */
	private function handle(Response|JsonResponse $response): array
	{
		$returned = (new ApiSecureHeaders())->handle(Request::create('/api/v2/Albums'), fn () => $response);
		self::assertSame($response, $returned);

		return (new SecureHeaders(config('secure-headers')))->headers();
	}
}
