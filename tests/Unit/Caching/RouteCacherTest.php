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

namespace Tests\Unit;

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
		$routeCacher = new RouteCacher();
		Log::shouldReceive('info')->once();
		Cache::put('key', 60);

		Log::shouldReceive('debug')->once()->with('CacheListener: Hit for key');
		Log::shouldReceive('info')->never();

		$routeCacher->remember('key', 'route', 60, function () {
			return 60;
		}, ['tags']);
	}

	public function testRouteCacherMiss(): void
	{
		$routeCacher = new RouteCacher();
		Log::shouldReceive('debug')->once()->with('CacheListener: Miss for key');
		Log::shouldReceive('debug')->once()->with('CacheListener: Miss for route');
		Log::shouldReceive('debug')->once()->with('CacheListener: Miss for T:tags');
		Log::shouldReceive('info')->once()->with('CacheListener: Writing key key');
		Log::shouldReceive('info')->once()->with('CacheListener: Writing key route');
		Log::shouldReceive('info')->once()->with('CacheListener: Writing key T:tags');

		$routeCacher->remember('key', 'route', 60, function () {
			return 60;
		}, ['tags']);
	}

	public function testRouteCacherForgetRouteException(): void
	{
		$routeCacher = new RouteCacher();
		Log::shouldReceive('info')->once();
		Cache::put('route', [60]);

		Log::shouldReceive('debug')->once()->with('CacheListener: Hit for route');

		$this->expectException(LycheeLogicException::class);
		$routeCacher->forgetRoute('route');
		Cache::forget('route');
	}

	public function testRouteCacherForgetRoute(): void
	{
		$routeCacher = new RouteCacher();
		Log::shouldReceive('info')->twice();
		Cache::put('route', ['forgetMe' => 'value']);
		Cache::put('forgetMe', 'value');

		Log::shouldReceive('debug')->once()->with('CacheListener: Hit for route');
		Log::shouldReceive('info')->once()->with('CacheListener: Forgetting key forgetMe');
		Log::shouldReceive('info')->once()->with('CacheListener: Forgetting key route');

		$routeCacher->forgetRoute('route');
	}

	public function testRouteCacherForgetTagException(): void
	{
		$routeCacher = new RouteCacher();
		Log::shouldReceive('info')->once();
		Cache::put('T:tag', [60]);

		Log::shouldReceive('debug')->once()->with('CacheListener: Hit for T:tag');

		$this->expectException(LycheeLogicException::class);
		$routeCacher->forgetTag('tag');
		Cache::forget('T:tag');
	}

	public function testRouteCacherForgetTag(): void
	{
		$routeCacher = new RouteCacher();
		Log::shouldReceive('info')->twice();
		Cache::put('T:tag', ['forgetMe' => 'value']);
		Cache::put('forgetMe', 'value');

		Log::shouldReceive('debug')->once()->with('CacheListener: Hit for T:tag');
		Log::shouldReceive('info')->once()->with('CacheListener: Forgetting key forgetMe');
		Log::shouldReceive('info')->once()->with('CacheListener: Forgetting key T:tag');

		$routeCacher->forgetTag('tag');
	}
}
