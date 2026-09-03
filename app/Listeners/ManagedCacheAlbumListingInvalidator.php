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
use App\Events\PhotoMoved;
use App\Events\PhotoPersonsChanged;
use App\Events\PhotoSaved;
use App\Events\PhotoWillBeDeleted;
use App\Events\TagAlbumSaved;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Support\Facades\DB;

/**
 * Translates every album-listing-relevant domain event into the precise (or,
 * for a handful of genuinely rare/subtree-wide operations, coarse) cache
 * tag(s) it invalidates.
 */
class ManagedCacheAlbumListingInvalidator
{
	public function __construct(
		private ManagedCacheService $cache,
		private CacheKeyProvider $cache_key_provider,
	) {
	}

	public function handleAlbumSaved(AlbumSaved $event): void
	{
		$this->cache->forgetTags($this->cache_key_provider->albumTags($event->album_ids));
		$this->cache->forgetTags($this->cache_key_provider->albumChildrenTags($event->parent_ids));
		$this->cache->forgetTag($this->cache_key_provider->albumChildrenTag(null));
		$this->cache->forgetTag($this->cache_key_provider->pinnedAlbumsListingTag());
		$this->cache->forgetTag($this->cache_key_provider->albumListingV3Tag());
	}

	public function handleAlbumDeleted(AlbumDeleted $event): void
	{
		$this->cache->forgetTag($this->cache_key_provider->albumChildrenTag($event->parent_id));
		$this->cache->forgetTag($this->cache_key_provider->albumChildrenTag(null));
		$this->cache->forgetTag($this->cache_key_provider->pinnedAlbumsListingTag());
		$this->cache->forgetTag($this->cache_key_provider->albumListingV3Tag());
	}

	public function handleAlbumChildrenChanged(AlbumChildrenChanged $event): void
	{
		$this->cache->forgetTags($this->cache_key_provider->albumChildrenTags($event->parent_ids));
		$this->cache->forgetTag($this->cache_key_provider->albumListingV3Tag());
	}

	public function handleTagAlbumSaved(TagAlbumSaved $event): void
	{
		$this->cache->forgetTags($this->cache_key_provider->albumTags($event->tag_album_ids));
		$this->cache->forgetTag($this->cache_key_provider->tagAlbumsListingTag());
		$this->cache->forgetTag($this->cache_key_provider->albumListingV3Tag());
	}

	public function handlePersonAlbumSaved(PersonAlbumSaved $event): void
	{
		$this->cache->forgetTag($this->cache_key_provider->albumTag($event->person_album->id));
		$this->cache->forgetTag($this->cache_key_provider->personAlbumsListingTag());
		$this->cache->forgetTag($this->cache_key_provider->albumListingV3Tag());
	}

	public function handleBaseAlbumRemoved(BaseAlbumRemoved $event): void
	{
		$this->cache->forgetTags($this->cache_key_provider->albumTags($event->base_album_ids));
		// Cheap, rare operation — not worth a type lookup, evict both.
		$this->cache->forgetTag($this->cache_key_provider->tagAlbumsListingTag());
		$this->cache->forgetTag($this->cache_key_provider->personAlbumsListingTag());
		$this->cache->forgetTag($this->cache_key_provider->albumListingV3Tag());
	}

	public function handleAccessPermissionChanged(AccessPermissionChanged $event): void
	{
		$this->cache->forgetTag($this->cache_key_provider->albumTag($event->base_album_id));
		$this->cache->forgetTag($this->cache_key_provider->albumListingV3Tag());

		// The rights endpoint's cache is keyed by
		// the *queried parent's own* albumChildrenTag(), and
		// can_delete_children/can_move_children/grants_* all derive from
		// access_permissions rows on that parent (or its direct children,
		// covered below via the grandparent's tag). A grant/share change
		// directly on $event->base_album_id must therefore also evict that
		// album's own children-tag, not just its parent's — the pre-061
		// invalidation below only ever evicted the latter, since v2/v3's
		// other album-listing endpoints have no such "rights of my own
		// direct children" cache to depend on it.
		$this->cache->forgetTag($this->cache_key_provider->albumChildrenTag($event->base_album_id));

		// The event payload only carries the id; resolve which of the three
		// album types it is via a lightweight, non-Eloquent lookup.
		$album = DB::table('albums')->where('id', '=', $event->base_album_id)->select('parent_id')->first();
		if ($album !== null) {
			$this->cache->forgetTag($this->cache_key_provider->albumChildrenTag($album->parent_id));
			$this->cache->forgetTag($this->cache_key_provider->albumChildrenTag(null));
			$this->cache->forgetTag($this->cache_key_provider->pinnedAlbumsListingTag());

			return;
		}

		if (DB::table('tag_albums')->where('id', '=', $event->base_album_id)->exists()) {
			$this->cache->forgetTag($this->cache_key_provider->tagAlbumsListingTag());

			return;
		}

		if (DB::table('person_albums')->where('id', '=', $event->base_album_id)->exists()) {
			$this->cache->forgetTag($this->cache_key_provider->personAlbumsListingTag());
		}
	}

	public function handleAlbumComputedDataUpdated(AlbumComputedDataUpdated $event): void
	{
		$this->cache->forgetTag($this->cache_key_provider->albumTag($event->album_id));
		$this->cache->forgetTag($this->cache_key_provider->albumListingV3Tag());
	}

	public function handleAlbumListingCacheFlushRequested(AlbumListingCacheFlushRequested $event): void
	{
		// Sufficient alone: every cached entry across all six query types
		// carries this tag in addition to its own specific tag(s).
		$this->cache->forgetTag($this->cache_key_provider->albumListingGlobalTag());
		// Feature 057's v3 listing cache is not tagged with the coarse global
		// tag above, so it must be evicted explicitly here too (FR-057-06).
		$this->cache->forgetTag($this->cache_key_provider->albumListingV3Tag());
	}

	public function handleAlbumTagsChanged(AlbumTagsChanged $event): void
	{
		$this->cache->forgetTags($this->cache_key_provider->albumTagTags($event->tag_ids));
		$this->cache->forgetTag($this->cache_key_provider->albumListingV3Tag());
	}

	/**
	 * A face was (re)assigned/unassigned/dismissed: the PersonAlbum "matching
	 * albums" cache (see AlbumRepository::getMatchingAlbumsForPersonPaginated())
	 * is keyed by person id and must be evicted for every person on either
	 * side of the change.
	 */
	public function handlePhotoPersonsChanged(PhotoPersonsChanged $event): void
	{
		$this->cache->forgetTags($this->cache_key_provider->albumPersonTags($event->person_ids));
		$this->cache->forgetTag($this->cache_key_provider->albumListingV3Tag());
	}

	/**
	 * A photo was moved between real albums: which albums now contain a
	 * given person's photos may have changed, even though the photo's own
	 * face/person assignments did not.
	 */
	public function handlePhotoMoved(PhotoMoved $event): void
	{
		$this->evictPersonTagsForPhotos($event->photo_ids);
		$this->cache->forgetTag($this->cache_key_provider->albumListingV3Tag());
	}

	/**
	 * A photo's `photo_album` link was (re)written — covers new uploads,
	 * imports, and photo copies (see MoveOrDuplicate::do()). Fires more
	 * broadly than strictly necessary (PhotoSaved is also dispatched for
	 * unrelated reasons, e.g. size-variant regeneration), but the eviction
	 * below is cheap.
	 */
	public function handlePhotoSaved(PhotoSaved $event): void
	{
		$this->evictPersonTagsForPhotos($event->photo_ids);
		$this->cache->forgetTag($this->cache_key_provider->albumListingV3Tag());
	}

	/**
	 * A photo is about to be removed from an album (detach) or hard-deleted;
	 * dispatched before the `faces` rows are gone, so they can still be
	 * resolved here.
	 */
	public function handlePhotoWillBeDeleted(PhotoWillBeDeleted $event): void
	{
		$this->evictPersonTagsForPhotos([$event->photo_id]);
		$this->cache->forgetTag($this->cache_key_provider->albumListingV3Tag());
	}

	/**
	 * Evicts the PersonAlbum "matching albums" cache for every person
	 * currently associated (via a non-dismissed face) with any of the given photos.
	 *
	 * @param string[] $photo_ids
	 */
	private function evictPersonTagsForPhotos(array $photo_ids): void
	{
		if ($photo_ids === []) {
			return;
		}

		$person_ids = DB::table('faces')
			->whereIn('photo_id', $photo_ids)
			->whereNotNull('person_id')
			->where('is_dismissed', '=', false)
			->distinct()
			->pluck('person_id')
			->all();

		if ($person_ids !== []) {
			$this->cache->forgetTags($this->cache_key_provider->albumPersonTags($person_ids));
		}
	}
}
