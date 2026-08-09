<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Events\AccessPermissionChanged;
use App\Events\AlbumChildrenChanged;
use App\Events\AlbumComputedDataUpdated;
use App\Events\AlbumDeleted;
use App\Events\AlbumListingCacheFlushRequested;
use App\Events\AlbumSaved;
use App\Events\AlbumTagsChanged;
use App\Events\BaseAlbumRemoved;
use App\Events\PersonAlbumSaved;
use App\Events\TagAlbumSaved;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Support\Facades\DB;

/**
 * Translates every album-listing-relevant domain event into the precise (or,
 * for a handful of genuinely rare/subtree-wide operations, coarse) cache
 * tag(s) it invalidates.
 *
 * Consolidates the tag scheme derived from Feature 053's mutation-surface
 * audit; see spec.md FR-053-22/FR-053-31 for the full mapping rationale.
 */
class ManagedCacheAlbumListingInvalidator
{
	public function __construct(
		private ManagedCacheService $cache,
	) {
	}

	public function handleAlbumSaved(AlbumSaved $event): void
	{
		$this->cache->forgetTag('album:' . $event->album->id);
		$this->cache->forgetTag('album-children:' . ($event->album->parent_id ?? 'root'));
		$this->cache->forgetTag('album-children:root');
		$this->cache->forgetTag('pinned-albums-listing');
	}

	public function handleAlbumDeleted(AlbumDeleted $event): void
	{
		$this->cache->forgetTag('album-children:' . ($event->parent_id ?? 'root'));
		$this->cache->forgetTag('album-children:root');
		$this->cache->forgetTag('pinned-albums-listing');
	}

	public function handleAlbumChildrenChanged(AlbumChildrenChanged $event): void
	{
		$this->cache->forgetTag('album-children:' . ($event->parent_id ?? 'root'));
	}

	public function handleTagAlbumSaved(TagAlbumSaved $event): void
	{
		$this->cache->forgetTag('album:' . $event->tag_album->id);
		$this->cache->forgetTag('tag-albums-listing');
	}

	public function handlePersonAlbumSaved(PersonAlbumSaved $event): void
	{
		$this->cache->forgetTag('album:' . $event->person_album->id);
		$this->cache->forgetTag('person-albums-listing');
	}

	public function handleBaseAlbumRemoved(BaseAlbumRemoved $event): void
	{
		$this->cache->forgetTag('album:' . $event->base_album_id);
		// Cheap, rare operation — not worth a type lookup, evict both.
		$this->cache->forgetTag('tag-albums-listing');
		$this->cache->forgetTag('person-albums-listing');
	}

	public function handleAccessPermissionChanged(AccessPermissionChanged $event): void
	{
		$this->cache->forgetTag('album:' . $event->base_album_id);

		// The event payload only carries the id; resolve which of the three
		// album types it is via a lightweight, non-Eloquent lookup.
		$album = DB::table('albums')->where('id', '=', $event->base_album_id)->select('parent_id')->first();
		if ($album !== null) {
			$this->cache->forgetTag('album-children:' . ($album->parent_id ?? 'root'));
			$this->cache->forgetTag('album-children:root');
			$this->cache->forgetTag('pinned-albums-listing');

			return;
		}

		if (DB::table('tag_albums')->where('id', '=', $event->base_album_id)->exists()) {
			$this->cache->forgetTag('tag-albums-listing');

			return;
		}

		if (DB::table('person_albums')->where('id', '=', $event->base_album_id)->exists()) {
			$this->cache->forgetTag('person-albums-listing');
		}
	}

	public function handleAlbumComputedDataUpdated(AlbumComputedDataUpdated $event): void
	{
		$this->cache->forgetTag('album:' . $event->album_id);
	}

	public function handleAlbumListingCacheFlushRequested(AlbumListingCacheFlushRequested $event): void
	{
		// Sufficient alone: every cached entry across all six query types
		// carries this tag in addition to its own specific tag(s).
		$this->cache->forgetTag('album-listing-global');
	}

	public function handleAlbumTagsChanged(AlbumTagsChanged $event): void
	{
		foreach ($event->tag_ids as $tag_id) {
			$this->cache->forgetTag('tag:' . $tag_id);
		}
	}
}
