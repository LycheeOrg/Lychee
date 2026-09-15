<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Events\MapListingCacheFlushRequested;
use App\Events\PhotoDeleted;
use App\Events\PhotoMoved;
use App\Events\PhotoSaved;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Support\Facades\DB;

/**
 * Translates every Map-cache-relevant domain event into the precise
 * {@see CacheKeyProvider::mapListingTag()} eviction(s) it implies
 * (FR-067-16, FR-067-24). Direct structural precedent:
 * {@see ManagedCachePhotoListingInvalidator}.
 */
class ManagedCacheMapListingInvalidator
{
	private const ROOT_SCOPE = 'root';

	public function __construct(
		private ManagedCacheService $cache,
		private CacheKeyProvider $cache_key_provider,
	) {
	}

	/**
	 * Root scope's tag is always evicted — it is coarse/scope-wide (covers
	 * every possible root-scope viewer), not per-photo, so any geotagged
	 * photo's save could affect it regardless of which photo changed. Each
	 * of the photo's containing albums' tag is evicted too, so any
	 * album-scoped map cache warm for one of them is refreshed as well.
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

		$this->cache->forgetTags($this->scopeTags($album_ids));
	}

	/**
	 * A cross-album move affects both the source and destination album's
	 * map caches, plus root's own coarse tag.
	 */
	public function handlePhotoMoved(PhotoMoved $event): void
	{
		$this->cache->forgetTags($this->scopeTags([$event->from_album_id, $event->to_album_id]));
	}

	/**
	 * A photo was removed from (or hard-deleted out of) one album.
	 */
	public function handlePhotoDeleted(PhotoDeleted $event): void
	{
		$this->cache->forgetTags($this->scopeTags([$event->album_id]));
	}

	/**
	 * FR-067-24: flushes every warm Map cache tag (root's, plus every warm
	 * album scope's) at once - sufficient because every Map cache entry
	 * carries {@see CacheKeyProvider::mapListingGlobalTag()} in addition to
	 * its own scope-specific tag.
	 */
	public function handleMapListingCacheFlushRequested(MapListingCacheFlushRequested $event): void
	{
		$this->cache->forgetTag($this->cache_key_provider->mapListingGlobalTag());
	}

	/**
	 * @param string[] $album_ids
	 *
	 * @return string[]
	 */
	private function scopeTags(array $album_ids): array
	{
		$tags = [$this->cache_key_provider->mapListingTag(self::ROOT_SCOPE)];
		foreach ($album_ids as $album_id) {
			$tags[] = $this->cache_key_provider->mapListingTag($album_id);
		}

		return $tags;
	}
}
