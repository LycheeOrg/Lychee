<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Events\AlbumPhotoSortingChanged;
use App\Events\PhotoBucketsRecomputed;
use App\Events\PhotoDeleted;
use App\Events\PhotoMoved;
use App\Events\PhotoSaved;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Support\Facades\DB;

/**
 * Translates every photo-listing-relevant domain event into the
 * {@see CacheKeyProvider::photoListingTag()} eviction(s) it implies. Direct
 * structural precedent: {@see ManagedCacheAlbumListingInvalidator}.
 */
class ManagedCachePhotoListingInvalidator
{
	public function __construct(
		private ManagedCacheService $cache,
		private CacheKeyProvider $cache_key_provider,
	) {
	}

	/**
	 * An upload, edit, or copy touched one or more photos' `photo_album`
	 * links and/or bucket-relevant columns — evict every album currently
	 * linking any of them. Fires more broadly than strictly necessary
	 * (`PhotoSaved` is also dispatched for unrelated reasons, e.g.
	 * size-variant regeneration), but the eviction below is cheap.
	 */
	public function handlePhotoSaved(PhotoSaved $event): void
	{
		if ($event->photo_ids === []) {
			return;
		}

		$album_ids = DB::table('photo_album')
			->whereIn('photo_id', $event->photo_ids)
			->distinct()
			->pluck('album_id')
			->all();

		if ($album_ids !== []) {
			$this->cache->forgetTags($this->cache_key_provider->photoListingTags($album_ids));
		}
	}

	/**
	 * A cross-album move affects both the source and destination album's
	 * photo listings.
	 */
	public function handlePhotoMoved(PhotoMoved $event): void
	{
		$this->cache->forgetTags($this->cache_key_provider->photoListingTags([$event->from_album_id, $event->to_album_id]));
	}

	/**
	 * A photo was removed from (or hard-deleted out of) one album.
	 */
	public function handlePhotoDeleted(PhotoDeleted $event): void
	{
		$this->cache->forgetTag($this->cache_key_provider->photoListingTag($event->album_id));
	}

	/**
	 * Dedicated signal for when an album's own `sorting_col`/
	 * `sorting_order`/`photo_timeline` changed, which
	 * {@see \App\Jobs\RecomputeAlbumPhotoBucketsJob} bulk-`upsert()`s
	 * every direct photo's `bucket_id` for, bypassing Eloquent events
	 * entirely - the photo-listing cache for that album must be evicted
	 * explicitly here.
	 */
	public function handleAlbumPhotoSortingChanged(AlbumPhotoSortingChanged $event): void
	{
		$this->cache->forgetTags($this->cache_key_provider->photoListingTags($event->album_ids));
	}

	/**
	 * Dedicated signal for {@see \App\Jobs\RecomputePhotoBucketsJob}, which
	 * bulk-`upsert()`s every linked album's `photo_album.bucket_id` for one
	 * photo, bypassing Eloquent events entirely - the photo-listing cache
	 * for every affected album must be evicted explicitly here.
	 */
	public function handlePhotoBucketsRecomputed(PhotoBucketsRecomputed $event): void
	{
		if ($event->album_ids === []) {
			return;
		}

		$this->cache->forgetTags($this->cache_key_provider->photoListingTags($event->album_ids));
	}
}
