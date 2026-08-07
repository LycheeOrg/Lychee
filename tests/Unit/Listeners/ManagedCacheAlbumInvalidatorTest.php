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

use App\Events\AccessPermissionChanged;
use App\Events\AlbumDeleted;
use App\Events\AlbumSaved;
use App\Events\PhotoAdded;
use App\Events\PhotoDeleted;
use App\Events\PhotoMoved;
use App\Events\PhotoSaved;
use App\Listeners\ManagedCacheAlbumInvalidator;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\User;
use App\Repositories\ConfigManager;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\AbstractTestCase;

class ManagedCacheAlbumInvalidatorTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private ManagedCacheService $cache_service;
	private ManagedCacheAlbumInvalidator $listener;
	private Album $root_album;
	private Album $child_album;

	public function setUp(): void
	{
		parent::setUp();
		Configs::set('managed_cache_enabled', '1');
		$this->cache_service = new ManagedCacheService(new ConfigManager());
		$this->listener = new ManagedCacheAlbumInvalidator($this->cache_service);

		$owner = User::factory()->create();
		$this->root_album = Album::factory()->as_root()->owned_by($owner)->create();
		$this->child_album = Album::factory()->children_of($this->root_album)->owned_by($owner)->create();
	}

	private function primeTag(string $tag): string
	{
		$key = 'mcai-test:' . $tag . ':' . uniqid();
		$this->cache_service->remember($key, [$tag], 60, fn () => 'value');

		return $key;
	}

	/**
	 * A minimal photo row + `photo_album` pivot entry, bypassing `PhotoFactory`'s
	 * `configure()` hook (7 `SizeVariant`s + a `Statistics` row via `Factory::create()`'s
	 * `afterCreating`) — this listener only needs a resolvable photo_id/album_id pivot
	 * row, not a fully-featured photo. `Photo::create()` (Eloquent, not the factory)
	 * still runs the model's own id-generation/`creating` machinery.
	 */
	private function createPhotoInAlbum(Album $album): Photo
	{
		$photo = new Photo();
		$photo->forceFill(Photo::factory()->raw(['owner_id' => $album->owner_id]));
		$photo->save();
		DB::table('photo_album')->insert(['photo_id' => $photo->id, 'album_id' => $album->id]);

		return $photo;
	}

	public function testHandleAlbumSavedEvictsAlbumAndParentTags(): void
	{
		$own_key = $this->primeTag('album:' . $this->child_album->id);
		$parent_key = $this->primeTag('album:' . $this->root_album->id);

		$this->listener->handleAlbumSaved(new AlbumSaved($this->child_album));

		self::assertNull(Cache::get($own_key));
		self::assertNull(Cache::get($parent_key));
	}

	public function testHandleAlbumDeletedEvictsOnlyParentTag(): void
	{
		$parent_key = $this->primeTag('album:' . $this->root_album->id);

		$this->listener->handleAlbumDeleted(new AlbumDeleted($this->root_album->id));

		self::assertNull(Cache::get($parent_key));
	}

	public function testHandleAlbumDeletedWithNoParentEvictsRootTag(): void
	{
		$root_key = $this->primeTag('album:root');

		$this->listener->handleAlbumDeleted(new AlbumDeleted(null));

		self::assertNull(Cache::get($root_key));
	}

	public function testHandleAccessPermissionChangedEvictsAlbumAndParentTags(): void
	{
		$own_key = $this->primeTag('album:' . $this->child_album->id);
		$parent_key = $this->primeTag('album:' . $this->root_album->id);

		$this->listener->handleAccessPermissionChanged(new AccessPermissionChanged($this->child_album->id));

		self::assertNull(Cache::get($own_key));
		self::assertNull(Cache::get($parent_key));
	}

	public function testHandlePhotoSavedResolvesAlbumViaPivotAndEvictsTags(): void
	{
		$photo = $this->createPhotoInAlbum($this->child_album);

		$own_key = $this->primeTag('album:' . $this->child_album->id);
		$parent_key = $this->primeTag('album:' . $this->root_album->id);

		$this->listener->handlePhotoSaved(new PhotoSaved($photo->id));

		self::assertNull(Cache::get($own_key));
		self::assertNull(Cache::get($parent_key));
	}

	public function testHandlePhotoAddedResolvesAlbumViaPivotAndEvictsTags(): void
	{
		$photo = $this->createPhotoInAlbum($this->child_album);

		$own_key = $this->primeTag('album:' . $this->child_album->id);

		$this->listener->handlePhotoAdded(new PhotoAdded($photo->id));

		self::assertNull(Cache::get($own_key));
	}

	public function testHandlePhotoDeletedEvictsAlbumAndParentTags(): void
	{
		$own_key = $this->primeTag('album:' . $this->child_album->id);
		$parent_key = $this->primeTag('album:' . $this->root_album->id);

		$this->listener->handlePhotoDeleted(new PhotoDeleted($this->child_album->id));

		self::assertNull(Cache::get($own_key));
		self::assertNull(Cache::get($parent_key));
	}

	public function testHandlePhotoMovedEvictsBothAlbumsAndTheirParents(): void
	{
		$from_key = $this->primeTag('album:' . $this->child_album->id);
		$to_key = $this->primeTag('album:' . $this->root_album->id);

		$this->listener->handlePhotoMoved(new PhotoMoved('unused-photo-id', $this->child_album->id, $this->root_album->id));

		self::assertNull(Cache::get($from_key));
		self::assertNull(Cache::get($to_key));
	}
}
