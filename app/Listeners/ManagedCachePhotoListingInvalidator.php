<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Constants\PersonAlbumPersons as PAP;
use App\Enum\SmartAlbumType;
use App\Events\AlbumPhotoSortingChanged;
use App\Events\PhotoBucketsRecomputed;
use App\Events\PhotoDeleted;
use App\Events\PhotoHighlightToggled;
use App\Events\PhotoMoved;
use App\Events\PhotoPersonsChanged;
use App\Events\PhotoRatingChanged;
use App\Events\PhotoSaved;
use App\Events\PhotoTagsChanged;
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

	/**
	 * A `TagAlbum`'s photo listing has no `photo_album` row to key off (see
	 * {@see \App\Actions\Photo\StructOfArrays\ResolvesPhotoSource}) - every
	 * `TagAlbum` whose tag set overlaps `$event->tag_ids` (the union of old
	 * and new tags, see {@see PhotoTagsChanged}) must be evicted, since the
	 * photo may have just entered or left its membership. The `untagged`
	 * smart album is evicted unconditionally too: a photo enters it when its
	 * last tag is removed and leaves it when its first tag is added, and
	 * `$event->tag_ids` alone can't tell which of those happened.
	 */
	public function handlePhotoTagsChanged(PhotoTagsChanged $event): void
	{
		if ($event->tag_ids === []) {
			return;
		}

		$tag_album_ids = DB::table('tag_albums_tags')
			->whereIn('tag_id', $event->tag_ids)
			->distinct()
			->pluck('album_id')
			->all();

		if ($tag_album_ids !== []) {
			$this->cache->forgetTags($this->cache_key_provider->photoListingTags($tag_album_ids));
		}

		$this->cache->forgetTag($this->cache_key_provider->photoListingTag(SmartAlbumType::UNTAGGED->value));
	}

	/**
	 * Same reasoning as {@see self::handlePhotoTagsChanged()}, for
	 * `PersonAlbum`. `$event->person_ids` is already the old/new union, so
	 * no "removed person" gap here.
	 */
	public function handlePhotoPersonsChanged(PhotoPersonsChanged $event): void
	{
		if ($event->person_ids === []) {
			return;
		}

		$person_album_ids = DB::table(PAP::PERSON_ALBUM_PERSONS)
			->whereIn(PAP::PERSON_ID, $event->person_ids)
			->distinct()
			->pluck('album_id')
			->all();

		if ($person_album_ids !== []) {
			$this->cache->forgetTags($this->cache_key_provider->photoListingTags($person_album_ids));
		}
	}

	/**
	 * Every built-in smart album whose condition reads `rating_avg` (see
	 * `App\SmartAlbums\{OneStarAlbum,...,FiveStarsAlbum,UnratedAlbum,
	 * BestPicturesAlbum,MyRatedPicturesAlbum,MyBestPicturesAlbum}`) must be
	 * evicted unconditionally - {@see PhotoRatingChanged} carries neither
	 * the old nor the new rating, and several of these conditions are
	 * threshold/rank-based (`best_pictures`, `my_best_pictures`), so which
	 * one(s) actually flipped can't be determined cheaply here. Mirrors
	 * {@see \App\Listeners\RecomputeAlbumUserThumbsOnPhotoChange}'s own
	 * unconditional smart-album refresh for the same reason.
	 */
	public function handlePhotoRatingChanged(PhotoRatingChanged $event): void
	{
		$this->cache->forgetTags($this->cache_key_provider->photoListingTags([
			SmartAlbumType::ONE_STAR->value,
			SmartAlbumType::TWO_STARS->value,
			SmartAlbumType::THREE_STARS->value,
			SmartAlbumType::FOUR_STARS->value,
			SmartAlbumType::FIVE_STARS->value,
			SmartAlbumType::UNRATED->value,
			SmartAlbumType::BEST_PICTURES->value,
			SmartAlbumType::MY_RATED_PICTURES->value,
			SmartAlbumType::MY_BEST_PICTURES->value,
		]));
	}

	/**
	 * Only the `highlighted` smart album's condition reads `is_highlighted`
	 * (`App\SmartAlbums\HighlightedAlbum`).
	 */
	public function handlePhotoHighlightToggled(PhotoHighlightToggled $event): void
	{
		$this->cache->forgetTag($this->cache_key_provider->photoListingTag(SmartAlbumType::HIGHLIGHTED->value));
	}
}
