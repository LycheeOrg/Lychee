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

use App\Listeners\CacheListener;
use App\Models\Configs;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

class CacheListenerTest extends AbstractTestCase
{
	public static function tearDownAfterClass(): void
	{
		Configs::where('key', 'cache_event_logging')->update(['value' => '0']);
		Configs::invalidateCache();
		parent::tearDownAfterClass();
	}

	public function testCacheListenerNever(): void
	{
		Log::shouldReceive('debug')->never();
		Log::shouldReceive('info')->never();

		Configs::where('key', 'cache_event_logging')->update(['value' => '0']);
		Configs::invalidateCache();

		$listener = new CacheListener();
		$listener->handle(new CacheMissed('store', 'key'));
		$listener->handle(new CacheMissed('store', 'lv:dev-lycheeOrg'));
	}

	public function testCacheListenerMissed(): void
	{
		Log::shouldReceive('debug')->once()->with('CacheListener: Miss for key');
		Log::shouldReceive('info')->never();

		Configs::where('key', 'cache_event_logging')->update(['value' => '1']);
		Configs::invalidateCache();

		$listener = new CacheListener();
		$listener->handle(new CacheMissed('store', 'key'));
	}

	public function testCacheListenerHit(): void
	{
		Log::shouldReceive('debug')->once()->with('CacheListener: Hit for key');
		Log::shouldReceive('info')->never();

		Configs::where('key', 'cache_event_logging')->update(['value' => '1']);
		Configs::invalidateCache();

		$listener = new CacheListener();
		$listener->handle(new CacheHit('store', 'key', 'value'));
	}

	public function testCacheListenerKeyForgotten(): void
	{
		Log::shouldReceive('debug')->never();
		Log::shouldReceive('info')->once()->with('CacheListener: Forgetting key key');

		Configs::where('key', 'cache_event_logging')->update(['value' => '1']);
		Configs::invalidateCache();

		$listener = new CacheListener();
		$listener->handle(new KeyForgotten('store', 'key'));
	}

	public function testCacheListenerKeyWritten(): void
	{
		Log::shouldReceive('debug')->never();
		Log::shouldReceive('info')->once()->with('CacheListener: Writing key key');

		Configs::where('key', 'cache_event_logging')->update(['value' => '1']);
		Configs::invalidateCache();

		$listener = new CacheListener();
		$listener->handle(new KeyWritten('store', 'key', 'value'));
	}

	public function testCacheListenerKeyWrittenApi(): void
	{
		Log::shouldReceive('info')->never();
		Log::shouldReceive('debug')->once()->with('CacheListener: Writing key api/key with value: \'value\'');

		Configs::where('key', 'cache_event_logging')->update(['value' => '1']);
		Configs::invalidateCache();

		$listener = new CacheListener();
		$listener->handle(new KeyWritten('store', 'api/key', 'value'));
	}
}
