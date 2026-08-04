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

namespace Tests\Unit\Middleware\Caching;

use App\Http\Middleware\Caching\ResponseCache;
use App\Metadata\Cache\RouteCacheManager;
use App\Metadata\Cache\RouteCacher;
use App\Repositories\ConfigManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\AbstractTestCase;

/**
 * RouteCacheManager is a final readonly class with a hard coded, DB free
 * route list, so we exercise the real implementation instead of mocking it.
 */
class ResponseCacheTest extends AbstractTestCase
{
	public function testSkipsNonGetRequests(): void
	{
		$request = Request::create('/api/v2/Album', 'POST');

		$route_cache_manager = new RouteCacheManager();
		$route_cacher = \Mockery::mock(RouteCacher::class);

		$middleware = new ResponseCache($route_cache_manager, $route_cacher);
		self::assertEquals('next', $middleware->handle($request, fn () => 'next'));
	}

	public function testSkipsWhenCacheDisabled(): void
	{
		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')->once()->with('cache_enabled')->andReturn(false);

		$request = Request::create('/api/v2/Album', 'GET');
		$request->attributes->set('configs', $config_manager);

		$route_cache_manager = new RouteCacheManager();
		$route_cacher = \Mockery::mock(RouteCacher::class);

		$middleware = new ResponseCache($route_cache_manager, $route_cacher);
		self::assertEquals('next', $middleware->handle($request, fn () => 'next'));
	}

	public function testSkipsWhenRouteIsNotCacheable(): void
	{
		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')->once()->with('cache_enabled')->andReturn(true);

		// 'api/v2/Album::getTargetListAlbums' is explicitly mapped to `false` (not cacheable).
		$request = Request::create('/api/v2/Album::getTargetListAlbums', 'GET');
		$request->attributes->set('configs', $config_manager);
		$route = new Route(['GET'], 'api/v2/Album::getTargetListAlbums', fn () => null);
		$request->setRouteResolver(fn () => $route);

		$route_cache_manager = new RouteCacheManager();
		$route_cacher = \Mockery::mock(RouteCacher::class);

		$middleware = new ResponseCache($route_cache_manager, $route_cacher);
		self::assertEquals('next', $middleware->handle($request, fn () => 'next'));
	}

	public function testCachesResponseWithExtraParameters(): void
	{
		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')->once()->with('cache_enabled')->andReturn(true);
		$config_manager->shouldReceive('getValueAsInt')->once()->with('cache_ttl')->andReturn(60);

		// 'api/v2/Album' is mapped with extra: ['album_id'].
		$request = Request::create('/api/v2/Album', 'GET', ['album_id' => 'abc123456789012345678901']);
		$request->attributes->set('configs', $config_manager);
		$route = new Route(['GET'], 'api/v2/Album', fn () => null);
		$request->setRouteResolver(fn () => $route);

		$route_cache_manager = new RouteCacheManager();
		$route_cacher = \Mockery::mock(RouteCacher::class);
		$route_cacher->shouldReceive('remember')
			->once()
			->with(\Mockery::type('string'), 'api/v2/Album', 60, \Mockery::type(\Closure::class), ['abc123456789012345678901'])
			->andReturnUsing(fn ($_key, $_route, $_ttl, \Closure $callback, $_extras) => $callback());

		$middleware = new ResponseCache($route_cache_manager, $route_cacher);
		self::assertEquals('next', $middleware->handle($request, fn () => 'next'));
	}
}
