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
	public function tearDown(): void
	{
		Configs::set('cache_event_logging', '0');

		parent::tearDown();
	}

	public function testCacheListenerNever(): void
	{
		Log::shouldReceive('debug')->never();
		Log::shouldReceive('info')->never();

		Configs::set('cache_event_logging', '0');

		$listener = new CacheListener();
		$listener->handle(new CacheMissed('store', 'key'));
		$listener->handle(new CacheMissed('store', 'lv:dev-lycheeOrg'));
	}

	public function testCacheListenerMissed(): void
	{
		Log::shouldReceive('debug')->once()->with('CacheListener: Miss for key');
		Log::shouldReceive('info')->never();

		Configs::set('cache_event_logging', '1');

		$listener = new CacheListener();
		$listener->handle(new CacheMissed('store', 'key'));
	}

	public function testCacheListenerHit(): void
	{
		Log::shouldReceive('debug')->once()->with('CacheListener: Hit for key');
		Log::shouldReceive('info')->never();

		Configs::set('cache_event_logging', '1');

		$listener = new CacheListener();
		$listener->handle(new CacheHit('store', 'key', 'value'));
	}

	public function testCacheListenerKeyForgotten(): void
	{
		Log::shouldReceive('debug')->never();
		Log::shouldReceive('info')->once()->with('CacheListener: Forgetting key key');

		Configs::set('cache_event_logging', '1');

		$listener = new CacheListener();
		$listener->handle(new KeyForgotten('store', 'key'));
	}

	public function testCacheListenerKeyWritten(): void
	{
		Log::shouldReceive('debug')->never();
		Log::shouldReceive('info')->once()->with('CacheListener: Writing key key');

		Configs::set('cache_event_logging', '1');

		$listener = new CacheListener();
		$listener->handle(new KeyWritten('store', 'key', 'value'));
	}

	public function testCacheListenerKeyWrittenApi(): void
	{
		Log::shouldReceive('info')->never();
		Log::shouldReceive('debug')->once()->with('CacheListener: Writing key api/key with value: \'value\'');

		Configs::set('cache_event_logging', '1');

		$listener = new CacheListener();
		$listener->handle(new KeyWritten('store', 'api/key', 'value'));
	}
}
