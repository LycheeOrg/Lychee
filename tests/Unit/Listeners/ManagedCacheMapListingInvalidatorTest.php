<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Listeners;

use App\Events\MapListingCacheFlushRequested;
use App\Events\PhotoDeleted;
use App\Events\PhotoMoved;
use App\Events\PhotoSaved;
use App\Listeners\ManagedCacheMapListingInvalidator;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use App\Repositories\ConfigManager;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\AbstractTestCase;

/**
 * Covers {@see ManagedCacheMapListingInvalidator} (T-067-19/20/36/37).
 * Direct structural precedent:
 * {@see \Tests\Unit\Listeners\ManagedCachePhotoListingInvalidatorTest}.
 */
class ManagedCacheMapListingInvalidatorTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private ManagedCacheService $cache_service;
	private CacheKeyProvider $cache_key_provider;
	private ManagedCacheMapListingInvalidator $listener;

	protected function setUp(): void
	{
		parent::setUp();
		config(['features.enable-caching' => true]);

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')->with('managed_cache_enabled')->andReturn(true);

		$this->cache_service = new ManagedCacheService($config_manager);
		$this->cache_key_provider = new CacheKeyProvider();
		$this->listener = new ManagedCacheMapListingInvalidator($this->cache_service, $this->cache_key_provider);
	}

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	/** @param string[] $tags */
	private function seedCache(string $key, array $tags): void
	{
		$this->cache_service->remember($key, $tags, fn () => 'value', ttl: 60);
	}

	private function assertEvicted(string $key): void
	{
		self::assertNull(Cache::get($key), "expected key [$key] to have been evicted");
	}

	private function assertNotEvicted(string $key): void
	{
		self::assertNotNull(Cache::get($key), "expected key [$key] to still be cached");
	}

	// ── PhotoSaved / PhotoMoved (S-067-10) ───────────────────────────

	public function testPhotoSavedEvictsRootAndEveryContainingAlbumTagButNotUnrelatedScope(): void
	{
		$user = User::factory()->create();
		$album_a = Album::factory()->as_root()->owned_by($user)->create();
		$unrelated = Album::factory()->as_root()->owned_by($user)->create();
		$photo = Photo::factory()->owned_by($user)->in($album_a)->create();

		$this->seedCache('k:root', [$this->cache_key_provider->mapListingTag('root')]);
		$this->seedCache('k:a', [$this->cache_key_provider->mapListingTag($album_a->id)]);
		$this->seedCache('k:unrelated', [$this->cache_key_provider->mapListingTag($unrelated->id)]);

		$this->listener->handlePhotoSaved(new PhotoSaved([$photo->id]));

		$this->assertEvicted('k:root');
		$this->assertEvicted('k:a');
		$this->assertNotEvicted('k:unrelated');
	}

	public function testPhotoSavedWithEmptyIdsIsNoOp(): void
	{
		$this->seedCache('k:root', [$this->cache_key_provider->mapListingTag('root')]);
		$this->listener->handlePhotoSaved(new PhotoSaved([]));
		$this->assertNotEvicted('k:root');
	}

	public function testPhotoMovedEvictsRootPlusSourceAndDestinationAlbumTags(): void
	{
		$user = User::factory()->create();
		$from = Album::factory()->as_root()->owned_by($user)->create();
		$to = Album::factory()->as_root()->owned_by($user)->create();
		$unrelated = Album::factory()->as_root()->owned_by($user)->create();
		$photo = Photo::factory()->owned_by($user)->in($to)->create();

		$this->seedCache('k:root', [$this->cache_key_provider->mapListingTag('root')]);
		$this->seedCache('k:from', [$this->cache_key_provider->mapListingTag($from->id)]);
		$this->seedCache('k:to', [$this->cache_key_provider->mapListingTag($to->id)]);
		$this->seedCache('k:unrelated', [$this->cache_key_provider->mapListingTag($unrelated->id)]);

		$this->listener->handlePhotoMoved(new PhotoMoved([$photo->id], $from->id, $to->id));

		$this->assertEvicted('k:root');
		$this->assertEvicted('k:from');
		$this->assertEvicted('k:to');
		$this->assertNotEvicted('k:unrelated');
	}

	// ── PhotoDeleted (S-067-11) ───────────────────────────────────────

	public function testPhotoDeletedEvictsRootAndTheContainingAlbumTagButNotUnrelatedScope(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$unrelated = Album::factory()->as_root()->owned_by($user)->create();

		$this->seedCache('k:root', [$this->cache_key_provider->mapListingTag('root')]);
		$this->seedCache('k:a', [$this->cache_key_provider->mapListingTag($album->id)]);
		$this->seedCache('k:unrelated', [$this->cache_key_provider->mapListingTag($unrelated->id)]);

		$this->listener->handlePhotoDeleted(new PhotoDeleted($album->id));

		$this->assertEvicted('k:root');
		$this->assertEvicted('k:a');
		$this->assertNotEvicted('k:unrelated');
	}

	// ── Config-change coarse flush (S-067-19, FR-067-24) ─────────────

	public function testMapListingCacheFlushRequestedEvictsEveryWarmScope(): void
	{
		$global_tag = $this->cache_key_provider->mapListingGlobalTag();
		$this->seedCache('k:root', [$this->cache_key_provider->mapListingTag('root'), $global_tag]);
		$this->seedCache('k:album-1', [$this->cache_key_provider->mapListingTag('album-1'), $global_tag]);
		$this->seedCache('k:album-2', [$this->cache_key_provider->mapListingTag('album-2'), $global_tag]);

		$this->listener->handleMapListingCacheFlushRequested(new MapListingCacheFlushRequested());

		$this->assertEvicted('k:root');
		$this->assertEvicted('k:album-1');
		$this->assertEvicted('k:album-2');
	}
}
