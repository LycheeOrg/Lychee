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

use App\Constants\PhotoAlbum as PA;
use App\Events\AlbumRouteCacheUpdated;
use App\Http\Middleware\Caching\AlbumRouteCacheRefresher;
use App\Repositories\ConfigManager;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\AbstractTestCase;

class AlbumRouteCacheRefresherTest extends AbstractTestCase
{
	public function testSkipsGetRequests(): void
	{
		Event::fake();
		$request = Request::create('/api/v2/Album', 'GET');
		$middleware = new AlbumRouteCacheRefresher();

		self::assertEquals('next', $middleware->handle($request, fn () => 'next'));
		Event::assertNotDispatched(AlbumRouteCacheUpdated::class);
	}

	public function testSkipsWhenCacheDisabled(): void
	{
		Event::fake();

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')->once()->with('cache_enabled')->andReturn(false);

		$request = Request::create('/api/v2/Album', 'POST');
		$request->attributes->set('configs', $config_manager);
		$middleware = new AlbumRouteCacheRefresher();

		self::assertEquals('next', $middleware->handle($request, fn () => 'next'));
		Event::assertNotDispatched(AlbumRouteCacheUpdated::class);
	}

	public function testSkipsWhenRouteIsNotInAllowList(): void
	{
		Event::fake();

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')->once()->with('cache_enabled')->andReturn(true);

		$request = Request::create('/api/v2/NotOnTheList', 'POST');
		$request->attributes->set('configs', $config_manager);
		$middleware = new AlbumRouteCacheRefresher();

		self::assertEquals('next', $middleware->handle($request, fn () => 'next'));
		Event::assertNotDispatched(AlbumRouteCacheUpdated::class);
	}

	public function testDispatchesEventsForAllResolvedAlbumIds(): void
	{
		Event::fake();

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')->once()->with('cache_enabled')->andReturn(true);

		$request = Request::create('/api/v2/Album', 'POST', [
			'album_id' => 'album-1',
			'album_ids' => ['album-2'],
			'parent_id' => 'parent-1',
			'photo_id' => 'photo-1',
			'photo_ids' => ['photo-2'],
		]);
		$request->attributes->set('configs', $config_manager);

		$photo_album_builder = \Mockery::mock(Builder::class);
		$photo_album_builder->shouldReceive('select')->once()->with(PA::ALBUM_ID)->andReturnSelf();
		$photo_album_builder->shouldReceive('whereIn')->once()->with(PA::PHOTO_ID, ['photo-2'])->andReturnSelf();
		$photo_album_builder->shouldReceive('orWhere')->once()->with(PA::PHOTO_ID, '=', 'photo-1')->andReturnSelf();
		$photo_album_builder->shouldReceive('distinct')->once()->andReturnSelf();
		$photo_album_builder->shouldReceive('pluck')->once()->with('album_id')->andReturnSelf();
		$photo_album_builder->shouldReceive('all')->once()->andReturn(['album-from-photo']);

		$albums_builder = \Mockery::mock(Builder::class);
		$albums_builder->shouldReceive('select')->once()->with('parent_id')->andReturnSelf();
		$albums_builder->shouldReceive('whereIn')->once()->with('id', ['album-2'])->andReturnSelf();
		$albums_builder->shouldReceive('orWhere')->once()->with('id', '=', 'album-1')->andReturnSelf();
		$albums_builder->shouldReceive('distinct')->once()->andReturnSelf();
		$albums_builder->shouldReceive('pluck')->once()->with('parent_id')->andReturnSelf();
		$albums_builder->shouldReceive('all')->once()->andReturn(['grandparent-1']);

		DB::shouldReceive('table')->once()->with(PA::PHOTO_ALBUM)->andReturn($photo_album_builder);
		DB::shouldReceive('table')->once()->with('albums')->andReturn($albums_builder);

		$middleware = new AlbumRouteCacheRefresher();
		self::assertEquals('next', $middleware->handle($request, fn () => 'next'));

		Event::assertDispatched(AlbumRouteCacheUpdated::class, 5);
		foreach (['album-1', 'album-2', 'parent-1', 'album-from-photo', 'grandparent-1'] as $expected_id) {
			Event::assertDispatched(fn (AlbumRouteCacheUpdated $event) => $event->album_id === $expected_id);
		}
	}

	public function testDispatchesNothingWhenNoAlbumRelatedInputPresent(): void
	{
		Event::fake();

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')->once()->with('cache_enabled')->andReturn(true);

		$request = Request::create('/api/v2/Album', 'POST');
		$request->attributes->set('configs', $config_manager);

		$middleware = new AlbumRouteCacheRefresher();
		self::assertEquals('next', $middleware->handle($request, fn () => 'next'));

		Event::assertNotDispatched(AlbumRouteCacheUpdated::class);
	}
}
