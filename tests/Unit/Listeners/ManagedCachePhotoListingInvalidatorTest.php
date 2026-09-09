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

namespace Tests\Unit\Listeners;

use App\Events\AlbumPhotoSortingChanged;
use App\Events\PhotoBucketsRecomputed;
use App\Events\PhotoDeleted;
use App\Events\PhotoMoved;
use App\Events\PhotoSaved;
use App\Listeners\ManagedCachePhotoListingInvalidator;
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
 * Covers {@see \App\Listeners\ManagedCachePhotoListingInvalidator}. Direct
 * structural precedent:
 * {@see \Tests\Unit\Listeners\ManagedCacheAlbumListingInvalidatorTest}.
 */
class ManagedCachePhotoListingInvalidatorTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private ManagedCacheService $cache_service;
	private CacheKeyProvider $cache_key_provider;
	private ManagedCachePhotoListingInvalidator $listener;

	protected function setUp(): void
	{
		parent::setUp();
		config(['features.enable-caching' => true]);

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')->with('managed_cache_enabled')->andReturn(true);

		$this->cache_service = new ManagedCacheService($config_manager);
		$this->cache_key_provider = new CacheKeyProvider();
		$this->listener = new ManagedCachePhotoListingInvalidator($this->cache_service, $this->cache_key_provider);
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

	// ── PhotoSaved ──────────────────────────────────────────────

	public function testPhotoSavedEvictsEveryAlbumThatPhotoIsLinkedInto(): void
	{
		$user = User::factory()->create();
		$album_a = Album::factory()->as_root()->owned_by($user)->create();
		$album_b = Album::factory()->as_root()->owned_by($user)->create();
		$unrelated = Album::factory()->as_root()->owned_by($user)->create();
		$photo = Photo::factory()->owned_by($user)->in($album_a)->create();
		$photo->albums()->attach($album_b->id);

		$this->seedCache('k:a', [$this->cache_key_provider->photoListingTag($album_a->id)]);
		$this->seedCache('k:b', [$this->cache_key_provider->photoListingTag($album_b->id)]);
		$this->seedCache('k:unrelated', [$this->cache_key_provider->photoListingTag($unrelated->id)]);

		$this->listener->handlePhotoSaved(new PhotoSaved([$photo->id]));

		$this->assertEvicted('k:a');
		$this->assertEvicted('k:b');
		$this->assertNotEvicted('k:unrelated');
	}

	public function testPhotoSavedWithEmptyIdsIsNoOp(): void
	{
		$this->seedCache('k:x', ['some-tag']);
		$this->listener->handlePhotoSaved(new PhotoSaved([]));
		$this->assertNotEvicted('k:x');
	}

	// ── PhotoMoved ──────────────────────────────────────────────

	public function testPhotoMovedEvictsBothSourceAndDestinationAlbums(): void
	{
		$user = User::factory()->create();
		$from = Album::factory()->as_root()->owned_by($user)->create();
		$to = Album::factory()->as_root()->owned_by($user)->create();
		$unrelated = Album::factory()->as_root()->owned_by($user)->create();

		$this->seedCache('k:from', [$this->cache_key_provider->photoListingTag($from->id)]);
		$this->seedCache('k:to', [$this->cache_key_provider->photoListingTag($to->id)]);
		$this->seedCache('k:unrelated', [$this->cache_key_provider->photoListingTag($unrelated->id)]);

		$this->listener->handlePhotoMoved(new PhotoMoved(['photo-1'], $from->id, $to->id));

		$this->assertEvicted('k:from');
		$this->assertEvicted('k:to');
		$this->assertNotEvicted('k:unrelated');
	}

	// ── PhotoDeleted ──────────────────────────────────────────────

	public function testPhotoDeletedEvictsThatAlbumOnly(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$unrelated = Album::factory()->as_root()->owned_by($user)->create();

		$this->seedCache('k:album', [$this->cache_key_provider->photoListingTag($album->id)]);
		$this->seedCache('k:unrelated', [$this->cache_key_provider->photoListingTag($unrelated->id)]);

		$this->listener->handlePhotoDeleted(new PhotoDeleted($album->id));

		$this->assertEvicted('k:album');
		$this->assertNotEvicted('k:unrelated');
	}

	// ── AlbumPhotoSortingChanged dedicated signal ─────────────────

	public function testAlbumPhotoSortingChangedEvictsListedAlbumsOnly(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$unrelated = Album::factory()->as_root()->owned_by($user)->create();

		$this->seedCache('k:album', [$this->cache_key_provider->photoListingTag($album->id)]);
		$this->seedCache('k:unrelated', [$this->cache_key_provider->photoListingTag($unrelated->id)]);

		$this->listener->handleAlbumPhotoSortingChanged(new AlbumPhotoSortingChanged([$album->id]));

		$this->assertEvicted('k:album');
		$this->assertNotEvicted('k:unrelated');
	}

	// ── PhotoBucketsRecomputed dedicated signal ───────────────────

	public function testPhotoBucketsRecomputedEvictsListedAlbumsOnly(): void
	{
		$user = User::factory()->create();
		$album_a = Album::factory()->as_root()->owned_by($user)->create();
		$album_b = Album::factory()->as_root()->owned_by($user)->create();
		$unrelated = Album::factory()->as_root()->owned_by($user)->create();

		$this->seedCache('k:a', [$this->cache_key_provider->photoListingTag($album_a->id)]);
		$this->seedCache('k:b', [$this->cache_key_provider->photoListingTag($album_b->id)]);
		$this->seedCache('k:unrelated', [$this->cache_key_provider->photoListingTag($unrelated->id)]);

		$this->listener->handlePhotoBucketsRecomputed(new PhotoBucketsRecomputed([$album_a->id, $album_b->id]));

		$this->assertEvicted('k:a');
		$this->assertEvicted('k:b');
		$this->assertNotEvicted('k:unrelated');
	}

	public function testPhotoBucketsRecomputedWithEmptyIdsIsNoOp(): void
	{
		$this->seedCache('k:x', ['some-tag']);
		$this->listener->handlePhotoBucketsRecomputed(new PhotoBucketsRecomputed([]));
		$this->assertNotEvicted('k:x');
	}
}
