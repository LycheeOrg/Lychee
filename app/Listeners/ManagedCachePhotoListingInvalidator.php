<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Constants\PersonAlbumPersons as PAP;
use App\Enum\ColumnSortingPhotoType;
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
use App\Repositories\ConfigManager;
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
	 * `TimelineAlbum` branch) and evicts its fine tag, plus
	 * {@see CacheKeyProvider::photoBucketsTierTag()} unconditionally once
	 * (FR-066-09/FR-066-10) — Timeline's `buckets` tier response
	 * (whole-library counts) depends on every photo, so it must be
	 * invalidated on every relevant save/move regardless; only the
	 * *other* cached buckets' `ratios`/`details` entries are spared
	 * (NFR-066-04). Evicting the dedicated tier tag rather than the coarse
	 * {@see CacheKeyProvider::photoListingTag()} is what spares them: the
	 * coarse tag is also carried by every `ratios`/`details` window, so
	 * evicting it would flush every cached window regardless of bucket.
	 *
	 * Carbon-free (`[[feedback_avoid_carbon_server_side]]`): reads the raw
	 * `created_at`/`taken_at` column value directly, truncated via
	 * {@see PhotoBucketComputer::truncateRawDate()}.
	 *
	 * A save that changes the configured sort column moves a photo OUT of
	 * its previous bucket - re-reading only the (already-updated) DB row
	 * only ever resolves the NEW bucket, leaving the old bucket's cached
	 * `ratios`/`details` entries stale until expiry. `$previous_dates` (when
	 * supplied by the caller, captured via `getRawOriginal()` before the
	 * save landed) lets us evict that old bucket's fine tag too. When a
	 * photo has an entry in `$previous_dates` but it doesn't cover the
	 * currently-configured sort column, the previous bucket can't be
	 * resolved at all - fall back to evicting the coarse
	 * {@see CacheKeyProvider::photoListingTag()} (not the tier tag, already
	 * evicted above unconditionally) since that coarse tag is also carried
	 * by every `ratios`/`details` window regardless of bucket.
	 *
	 * @param array<int,string>                                          $photo_ids
	 * @param array<string,array{created_at?:?string,taken_at?:?string}> $previous_dates keyed by photo id, see {@see \App\Events\PhotoSaved::$previous_dates}
	 */
	private function evictTimelineBucketsFor(array $photo_ids, array $previous_dates = []): void
	{
		if ($photo_ids === []) {
			return;
		}

		$order = $this->config_manager->getValueAsEnum('timeline_photos_order', ColumnSortingPhotoType::class);
		if (!in_array($order, [ColumnSortingPhotoType::CREATED_AT, ColumnSortingPhotoType::TAKEN_AT], true)) {
			$order = ColumnSortingPhotoType::TAKEN_AT;
		}
		$granularity = $this->bucket_computer->resolveGranularity(null);

		$rows = DB::table('photos')->whereIn('id', $photo_ids)->select(['id', $order->value])->get();

		$tags = [$this->cache_key_provider->photoBucketsTierTag(self::TIMELINE_ALBUM_ID)];
		foreach ($rows as $row) {
			/** @var string|null $raw_date */
			$raw_date = $row->{$order->value};
			$bucket_id = $raw_date === null ? 'unknown' : $this->bucket_computer->truncateRawDate($raw_date, $granularity);
			$tags[] = $this->cache_key_provider->photoListingBucketTag(self::TIMELINE_ALBUM_ID, $bucket_id);

			if (array_key_exists($row->id, $previous_dates)) {
				$previous_entry = $previous_dates[$row->id];
				if (array_key_exists($order->value, $previous_entry)) {
					/** @var string|null $previous_raw */
					$previous_raw = $previous_entry[$order->value];
					$previous_bucket_id = $previous_raw === null ? 'unknown' : $this->bucket_computer->truncateRawDate($previous_raw, $granularity);
					if ($previous_bucket_id !== $bucket_id) {
						$tags[] = $this->cache_key_provider->photoListingBucketTag(self::TIMELINE_ALBUM_ID, $previous_bucket_id);
					}
				} else {
					$tags[] = $this->cache_key_provider->photoListingTag(self::TIMELINE_ALBUM_ID);
				}
			}
		}

		$this->cache->forgetTags(array_unique($tags));
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
