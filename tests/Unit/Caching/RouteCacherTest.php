<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Unit\Caching;

use App\Exceptions\Internal\LycheeLogicException;
use App\Metadata\Cache\RouteCacher;
use App\Models\Configs;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

class RouteCacherTest extends AbstractTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		// We log to make sure to catch the specific events.
		Configs::where('key', 'cache_event_logging')->update(['value' => '1']);
		Configs::invalidateCache();
	}

	public function tearDown(): void
	{
		Configs::where('key', 'cache_event_logging')->update(['value' => '0']);
		Configs::invalidateCache();
		parent::tearDown();
	}

	public function testRouteCacherHit(): void
	{
		$route_cacher = new RouteCacher();
		Log::shouldReceive('info')->once();
		Cache::put('key', 60);

		Log::shouldReceive('debug')->once()->with('CacheListener: Hit for key');
		Log::shouldReceive('info')->never();

		$route_cacher->remember('key', 'route', 60, function () {
			return 60;
		}, ['tags']);
	}

	public function testRouteCacherMiss(): void
	{
		$route_cacher = new RouteCacher();
		Log::shouldReceive('debug')->once()->with('CacheListener: Miss for key');
		Log::shouldReceive('debug')->once()->with('CacheListener: Miss for route');
		Log::shouldReceive('debug')->once()->with('CacheListener: Miss for T:tags');
		Log::shouldReceive('info')->once()->with('CacheListener: Writing key key');
		Log::shouldReceive('info')->once()->with('CacheListener: Writing key route');
		Log::shouldReceive('info')->once()->with('CacheListener: Writing key T:tags');

		$route_cacher->remember('key', 'route', 60, function () {
			return 60;
		}, ['tags']);
	}

	public function testRouteCacherForgetRouteException(): void
	{
		$route_cacher = new RouteCacher();
		Log::shouldReceive('info')->once();
		Cache::put('route', [60]);

		Log::shouldReceive('debug')->once()->with('CacheListener: Hit for route');

		$this->expectException(LycheeLogicException::class);
		$route_cacher->forgetRoute('route');
		Cache::forget('route');
	}

	public function testRouteCacherForgetRoute(): void
	{
		$route_cacher = new RouteCacher();
		Log::shouldReceive('info')->twice();
		Cache::put('route', ['forgetMe' => 'value']);
		Cache::put('forgetMe', 'value');

		Log::shouldReceive('debug')->once()->with('CacheListener: Hit for route');
		Log::shouldReceive('info')->once()->with('CacheListener: Forgetting key forgetMe');
		Log::shouldReceive('info')->once()->with('CacheListener: Forgetting key route');

		$route_cacher->forgetRoute('route');
	}

	public function testRouteCacherForgetTagException(): void
	{
		$route_cacher = new RouteCacher();
		Log::shouldReceive('info')->once();
		Cache::put('T:tag', [60]);

		Log::shouldReceive('debug')->once()->with('CacheListener: Hit for T:tag');

		$this->expectException(LycheeLogicException::class);
		$route_cacher->forgetTag('tag');
		Cache::forget('T:tag');
	}

	public function testRouteCacherForgetTag(): void
	{
		$route_cacher = new RouteCacher();
		Log::shouldReceive('info')->twice();
		Cache::put('T:tag', ['forgetMe' => 'value']);
		Cache::put('forgetMe', 'value');

		Log::shouldReceive('debug')->once()->with('CacheListener: Hit for T:tag');
		Log::shouldReceive('info')->once()->with('CacheListener: Forgetting key forgetMe');
		Log::shouldReceive('info')->once()->with('CacheListener: Forgetting key T:tag');

		$route_cacher->forgetTag('tag');
	}
}
