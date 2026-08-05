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

use App\Http\Middleware\VerifyCsrfToken;
use App\Services\Auth\SessionOrTokenGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Config;
use Tests\AbstractTestCase;

class VerifyCsrfTokenTest extends AbstractTestCase
{
	private function callIsReading(VerifyCsrfToken $middleware, Request $request): bool
	{
		$method = new \ReflectionMethod(VerifyCsrfToken::class, 'isReading');

		return $method->invoke($middleware, $request);
	}

	public function testBypassesParentWhenTokenHeaderPresent(): void
	{
		$request = Request::create('/api/v2/Foo', 'POST', [], [], [], [
			'HTTP_' . strtoupper(str_replace('-', '_', SessionOrTokenGuard::HTTP_TOKEN_HEADER)) => 'some-token',
		]);

		$middleware = app(VerifyCsrfToken::class);
		$response = $middleware->handle($request, fn () => 'passed-through');

		self::assertEquals('passed-through', $response);
	}

	public function testFallsThroughToParentWhenNoTokenHeader(): void
	{
		$request = Request::create('/web/foo', 'GET');
		$session = app('session')->driver('array');
		$request->setLaravelSession($session);
		$route = new Route(['GET'], '/web/foo', fn () => null);
		$request->setRouteResolver(fn () => $route);

		$middleware = app(VerifyCsrfToken::class);
		$response = $middleware->handle($request, fn () => new \Illuminate\Http\Response('passed-through'));

		self::assertEquals('passed-through', $response->getContent());
	}

	public function testIsReadingFalseForApiV2RouteWhenViteProxyDisabled(): void
	{
		Config::set('features.vite-http-proxy', false);

		$request = Request::create('/api/v2/Foo', 'POST');
		$route = new Route(['POST'], 'api/v2/Foo', fn () => null);
		$request->setRouteResolver(fn () => $route);

		$middleware = app(VerifyCsrfToken::class);
		self::assertFalse($this->callIsReading($middleware, $request));
	}

	public function testIsReadingFallsBackToParentForNonApiRoute(): void
	{
		Config::set('features.vite-http-proxy', false);

		$request = Request::create('/web/foo', 'GET');
		$route = new Route(['GET'], 'web/foo', fn () => null);
		$request->setRouteResolver(fn () => $route);

		$middleware = app(VerifyCsrfToken::class);
		self::assertTrue($this->callIsReading($middleware, $request));
	}

	public function testIsReadingFallsBackToParentForApiRouteWhenViteProxyEnabled(): void
	{
		Config::set('features.vite-http-proxy', true);

		$request = Request::create('/api/v2/Foo', 'POST');
		$route = new Route(['POST'], 'api/v2/Foo', fn () => null);
		$request->setRouteResolver(fn () => $route);

		$middleware = app(VerifyCsrfToken::class);
		self::assertFalse($this->callIsReading($middleware, $request));
	}
}
