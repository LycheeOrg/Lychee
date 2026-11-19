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

use App\Constants\RandomID;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Middleware\ResolveAlbumSlug;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Tests\AbstractTestCase;

/**
 * Complements {@see ResolveAlbumSlugTest} (a real-DB feature test covering
 * the main API paths) with mocked, DB-free tests for the branches only
 * reachable via the `albumId`/`album_id` route parameters and via edge-case
 * input values (empty strings mixed into arrays).
 */
class ResolveAlbumSlugMiddlewareTest extends AbstractTestCase
{
	public function testResolvesSlugFromRouteParameter(): void
	{
		$builder = \Mockery::mock(Builder::class);
		$builder->shouldReceive('where')->once()->with('slug', '=', 'my-slug')->andReturnSelf();
		$builder->shouldReceive('value')->once()->with('id')->andReturn('resolvedid123456789012345');
		DB::shouldReceive('table')->once()->with('base_albums')->andReturn($builder);

		$request = Request::create('/gallery/my-slug', 'GET');
		$route = new Route(['GET'], 'gallery/{albumId?}', fn () => null);
		$route->bind($request);
		$request->setRouteResolver(fn () => $route);

		$middleware = new ResolveAlbumSlug();
		$middleware->handle($request, fn () => new Response('ok'));

		self::assertEquals('resolvedid123456789012345', $route->parameter('albumId'));
	}

	/**
	 * The v3 API (and a handful of v2 routes such as
	 * `/Album/{album_id}/people`) spell the route segment `album_id`, not
	 * `albumId`, and read it back with `$this->route('album_id')` in
	 * `prepareForValidation()`. `Request::input()` never sees route
	 * parameters, so this shape needs its own explicit resolution pass.
	 */
	public function testResolvesSlugFromSnakeCaseRouteParameter(): void
	{
		$builder = \Mockery::mock(Builder::class);
		$builder->shouldReceive('where')->once()->with('slug', '=', 'my-slug')->andReturnSelf();
		$builder->shouldReceive('value')->once()->with('id')->andReturn('resolvedid123456789012345');
		DB::shouldReceive('table')->once()->with('base_albums')->andReturn($builder);

		$request = Request::create('/api/v3/Albums/my-slug/buckets', 'GET');
		$route = new Route(['GET'], 'api/v3/Albums/{album_id}/buckets', fn () => null);
		$route->bind($request);
		$request->setRouteResolver(fn () => $route);

		$middleware = new ResolveAlbumSlug();
		$middleware->handle($request, fn () => new Response('ok'));

		self::assertEquals('resolvedid123456789012345', $route->parameter(RequestAttribute::ALBUM_ID_ATTRIBUTE));
	}

	/**
	 * A real ID of {@see \App\Constants\RandomID::ID_LENGTH} characters in a
	 * snake_case route segment must pass through untouched and without
	 * hitting the database.
	 */
	public function testLeavesRealIdInSnakeCaseRouteParameterUntouched(): void
	{
		DB::shouldReceive('table')->never();

		$real_id = str_repeat('a', RandomID::ID_LENGTH);
		$request = Request::create('/api/v3/Albums/' . $real_id, 'GET');
		$route = new Route(['GET'], 'api/v3/Albums/{album_id}', fn () => null);
		$route->bind($request);
		$request->setRouteResolver(fn () => $route);

		$middleware = new ResolveAlbumSlug();
		$middleware->handle($request, fn () => new Response('ok'));

		self::assertEquals($real_id, $route->parameter(RequestAttribute::ALBUM_ID_ATTRIBUTE));
	}

	public function testSkipsEmptyStringParamsAndInvalidArrayItems(): void
	{
		$builder = \Mockery::mock(Builder::class);
		$builder->shouldReceive('where')->once()->with('slug', '=', 'valid-slug')->andReturnSelf();
		$builder->shouldReceive('value')->once()->with('id')->andReturn('resolvedid123456789012345');
		DB::shouldReceive('table')->once()->with('base_albums')->andReturn($builder);

		$request = Request::create('/api/v2/Album::move', 'POST', [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => '',
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => ['', 'valid-slug'],
		]);

		$middleware = new ResolveAlbumSlug();
		$middleware->handle($request, fn () => new Response('ok'));

		self::assertEquals('', $request->input(RequestAttribute::ALBUM_ID_ATTRIBUTE));
		self::assertEquals(['', 'resolvedid123456789012345'], $request->input(RequestAttribute::ALBUM_IDS_ATTRIBUTE));
	}
}
