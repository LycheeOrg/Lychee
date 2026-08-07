<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Constants\PhotoAlbum as PA;
use App\Events\AccessPermissionChanged;
use App\Events\AlbumDeleted;
use App\Events\AlbumSaved;
use App\Events\PhotoAdded;
use App\Events\PhotoDeleted;
use App\Events\PhotoMoved;
use App\Events\PhotoSaved;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Support\Facades\DB;

/**
 * Evicts ManagedCacheService tags for an album and its immediate parent whenever
 * something changes that could affect a cached listing of that album's children
 * or photos (FR-052-06).
 */
class ManagedCacheAlbumInvalidator
{
	private const PREFIX = 'album:';

	public function __construct(
		private ManagedCacheService $managed_cache_service,
	) {
	}

	public function handleAlbumSaved(AlbumSaved $event): void
	{
		$this->evictAlbumAndParent($event->album->id, $event->album->parent_id);
	}

	/**
	 * `AlbumDeleted` carries only the deleted album's parent id, not its own id
	 * (the row is already gone by the time the event fires). Only the parent's
	 * tag is evicted, which is functionally sufficient: nothing can ever query
	 * a deleted album's own cached listings again (Q-052-06, Option A).
	 */
	public function handleAlbumDeleted(AlbumDeleted $event): void
	{
		$this->managed_cache_service->forgetTag(self::PREFIX . ($event->parent_id ?? 'root'));
	}

	public function handleAccessPermissionChanged(AccessPermissionChanged $event): void
	{
		$this->evictAlbumAndParentById($event->base_album_id);
	}

	public function handlePhotoSaved(PhotoSaved $event): void
	{
		$this->evictAlbumsForPhoto($event->photo_id);
	}

	public function handlePhotoAdded(PhotoAdded $event): void
	{
		$this->evictAlbumsForPhoto($event->photo_id);
	}

	public function handlePhotoDeleted(PhotoDeleted $event): void
	{
		$this->evictAlbumAndParentById($event->album_id);
	}

	public function handlePhotoMoved(PhotoMoved $event): void
	{
		$this->evictAlbumAndParentById($event->from_album_id);
		$this->evictAlbumAndParentById($event->to_album_id);
	}

	/**
	 * Resolve a photo to its containing album(s) via the `photo_album` pivot,
	 * mirroring `AlbumRouteCacheRefresher::handle()`.
	 */
	private function evictAlbumsForPhoto(string $photo_id): void
	{
		$album_ids = DB::table(PA::PHOTO_ALBUM)
			->select(PA::ALBUM_ID)
			->where(PA::PHOTO_ID, '=', $photo_id)
			->distinct()
			->pluck('album_id')
			->all();

		foreach ($album_ids as $album_id) {
			/** @var string $album_id */
			$this->evictAlbumAndParentById($album_id);
		}
	}

	private function evictAlbumAndParentById(string $album_id): void
	{
		// Plain query builder (not the Eloquent `Album` model) to avoid pulling in
		// `Album`'s eager-loaded relations for what is otherwise a single-column read.
		$parent_id = DB::table('albums')->where('id', '=', $album_id)->value('parent_id');
		/** @var string|null $parent_id */
		$this->evictAlbumAndParent($album_id, $parent_id);
	}

	private function evictAlbumAndParent(string $album_id, ?string $parent_id): void
	{
		$this->managed_cache_service->forgetTag(self::PREFIX . $album_id);
		$this->managed_cache_service->forgetTag(self::PREFIX . ($parent_id ?? 'root'));
	}
}
