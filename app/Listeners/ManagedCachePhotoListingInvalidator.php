<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Constants\PersonAlbumPersons as PAP;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\ColumnSortingType;
use App\Enum\SmartAlbumType;
use App\Events\AlbumPhotoSortingChanged;
use App\Events\PhotoBucketsRecomputed;
use App\Events\PhotoDeleted;
use App\Events\PhotoHighlightToggled;
use App\Events\PhotoListingCacheFlushRequested;
use App\Events\PhotoMoved;
use App\Events\PhotoPersonsChanged;
use App\Events\PhotoRatingChanged;
use App\Events\PhotoSaved;
use App\Events\PhotoTagsChanged;
use App\Repositories\ConfigManager;
use App\Services\BucketKeys;
use App\Services\BucketScheme;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use App\Services\PhotoBucketComputer;
use Illuminate\Support\Facades\DB;

/**
 * Translates every photo-listing-relevant domain event into the
 * {@see CacheKeyProvider::photoListingTag()} eviction(s) it implies. Direct
 * structural precedent: {@see ManagedCacheAlbumListingInvalidator}.
 */
class ManagedCachePhotoListingInvalidator
{
	private const TIMELINE_ALBUM_ID = SmartAlbumType::TIMELINE->value;

	public function __construct(
		private ManagedCacheService $cache,
		private CacheKeyProvider $cache_key_provider,
		private ConfigManager $config_manager,
		private PhotoBucketComputer $bucket_computer,
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

		$this->evictTimelineBucketsFor($event->photo_ids, $event->previous_dates);
	}

	/**
	 * A cross-album move affects both the source and destination album's
	 * photo listings, plus (FR-066-10) Timeline's own listing - a moved
	 * photo's `created_at`/`taken_at` (and therefore its Timeline bucket)
	 * is untouched by the move itself, but its real-album membership -
	 * which can affect Timeline visibility via NSFW-sensitive-album
	 * filtering - has changed.
	 */
	public function handlePhotoMoved(PhotoMoved $event): void
	{
		$this->cache->forgetTags($this->cache_key_provider->photoListingTags([$event->from_album_id, $event->to_album_id]));
		$this->evictTimelineBucketsFor($event->photo_ids);
	}

	/**
	 * A photo was removed from (or hard-deleted out of) one album, plus
	 * (FR-066-10) Timeline's coarse tag only - `PhotoDeleted` carries no
	 * `photo_ids`, so the deleted photo's Timeline bucket can no longer be
	 * resolved to evict just its fine tag; documented tradeoff (S-066-11).
	 */
	public function handlePhotoDeleted(PhotoDeleted $event): void
	{
		$this->cache->forgetTags([
			$this->cache_key_provider->photoListingTag($event->album_id),
			$this->cache_key_provider->photoListingTag(self::TIMELINE_ALBUM_ID),
		]);
	}

	/**
	 * Resolves each of `$photo_ids`' current Timeline bucket (per the
	 * instance-wide `timeline_photos_order`/`timeline_photos_granularity`
	 * config, mirroring {@see \App\Actions\Photo\StructOfArrays\ResolvesPhotoSource::resolveEffectiveSorting()}'s
	 * `TimelineAlbum` branch) from its `taken_key` or `created_at`
	 * (FR-092-17) and evicts its fine tag, plus
	 * {@see CacheKeyProvider::photoBucketsTierTag()} unconditionally once
	 * (FR-066-09/FR-066-10): Timeline's `buckets` tier depends on every
	 * photo; only the other cached buckets' `ratios`/`details` entries are
	 * spared (NFR-066-04).
	 *
	 * A save that moves a photo out of its previous bucket leaves that
	 * bucket's cached windows stale: `$previous_dates` (captured via
	 * `getRawOriginal()` before the save) resolves the previous bucket too.
	 * When a photo's entry does not cover the configured sort column, the
	 * previous bucket is unknown and the coarse
	 * {@see CacheKeyProvider::photoListingTag()} is evicted instead.
	 *
	 * @param array<int,string>                                           $photo_ids
	 * @param array<string,array{created_at?:?string,taken_key?:?string}> $previous_dates keyed by photo id, see {@see \App\Events\PhotoSaved::$previous_dates}
	 */
	private function evictTimelineBucketsFor(array $photo_ids, array $previous_dates = []): void
	{
		if ($photo_ids === []) {
			return;
		}

		$order = $this->config_manager->getValueAsEnum('timeline_photos_order', ColumnSortingPhotoType::class);
		$column = $order === ColumnSortingPhotoType::CREATED_AT ? ColumnSortingType::CREATED_AT : ColumnSortingType::TAKEN_AT;
		$key_column = $column === ColumnSortingType::CREATED_AT ? 'created_at' : 'taken_key';
		$scheme = BucketScheme::forPhotos($column, $this->bucket_computer->resolveGranularity(null), $this->config_manager);
		$bucket_id_of = fn (?string $raw): string => $scheme->idOfKey(($raw !== null && $column === ColumnSortingType::CREATED_AT) ? BucketKeys::createdKey($raw) : $raw);

		$rows = DB::table('photos')->whereIn('id', $photo_ids)->select(['id', $key_column])->get();

		$tags = [$this->cache_key_provider->photoBucketsTierTag(self::TIMELINE_ALBUM_ID)];
		foreach ($rows as $row) {
			$bucket_id = $bucket_id_of($row->{$key_column});
			$tags[] = $this->cache_key_provider->photoListingBucketTag(self::TIMELINE_ALBUM_ID, $bucket_id);

			if (!array_key_exists($row->id, $previous_dates)) {
				continue;
			}

			$previous_entry = $previous_dates[$row->id];
			$tags[] = array_key_exists($key_column, $previous_entry)
				? $this->cache_key_provider->photoListingBucketTag(self::TIMELINE_ALBUM_ID, $bucket_id_of($previous_entry[$key_column]))
				: $this->cache_key_provider->photoListingTag(self::TIMELINE_ALBUM_ID);
		}

		$this->cache->forgetTags(array_values(array_unique($tags)));
	}

	/**
	 * Dedicated signal for when an album's own `sorting_col`/
	 * `sorting_order`/`photo_timeline` changed, which
	 * {@see \App\Jobs\RecomputeAlbumPhotoBucketsJob} bulk-`upsert()`s
	 * every direct photo's `bucket_key` for, bypassing Eloquent events
	 * entirely - the photo-listing cache for that album must be evicted
	 * explicitly here.
	 */
	public function handleAlbumPhotoSortingChanged(AlbumPhotoSortingChanged $event): void
	{
		$this->cache->forgetTags($this->cache_key_provider->photoListingTags($event->album_ids));
	}

	/**
	 * Instance-wide photo bucket setting changed (FR-092-13): every cached
	 * photo listing carries the global tag.
	 */
	public function handlePhotoListingCacheFlushRequested(PhotoListingCacheFlushRequested $event): void
	{
		$this->cache->forgetTag($this->cache_key_provider->photoListingGlobalTag());
	}

	/**
	 * Dedicated signal for {@see \App\Jobs\RecomputePhotoBucketsJob}, which
	 * bulk-`upsert()`s every linked album's `photo_album.bucket_key` for one
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
