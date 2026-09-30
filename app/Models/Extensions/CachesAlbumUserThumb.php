<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models\Extensions;

use App\Models\AlbumUserThumb;
use Illuminate\Support\Facades\Auth;

/**
 * Adds a per-viewer thumb cache read-through to a tag/person/smart album.
 *
 * The cache is keyed by (album cache key, viewer), where the viewer is
 * either a registered user or `null` for the public/guest view of the
 * album. A cache miss falls back to the live query, then seeds the cache
 * so subsequent requests - by this same viewer, or any guest if the album
 * is public - hit the cache instead.
 *
 * The cache is kept warm on write events by {@link \App\Jobs\RecomputeAlbumUserThumbsJob},
 * which only refreshes viewers who already have a row (see that class for why).
 *
 * {@link \App\Models\TagAlbum} and {@link \App\Models\PersonAlbum} additionally
 * expose this cache row as a proper `userThumbRow()` relation, so a whole list
 * of tag/person albums can eager-load their thumb in a single query instead of
 * once per album; this trait's per-instance query remains the fallback for
 * whenever that relation wasn't eager-loaded, and is the only path available
 * to smart albums (which have no such relation, see {@link \App\SmartAlbums\BaseSmartAlbum}).
 */
trait CachesAlbumUserThumb
{
	/**
	 * @param string   $album_cache_key base_albums.id or a SmartAlbumType value
	 * @param \Closure $compute_live    computes the thumb live, on a cache miss
	 *
	 * @return ?Thumb
	 */
	/**
	 * Return the viewer's cached cover of `$album_cache_key`, or compute
	 * ranks 1–3 live via `$compute_live` (which must return the ordered
	 * `list<Thumb>` of {@see Thumb::createManyFromQueryable()}), seed the
	 * three cache columns (Feature 075, FR-075-08) and return rank 1.
	 *
	 * @param \Closure(): list<Thumb> $compute_live
	 */
	private function getCachedOrLiveThumb(string $album_cache_key, \Closure $compute_live): ?Thumb
	{
		$user_id = Auth::id();

		$cached = AlbumUserThumb::query()
			->with('photo.size_variants')
			->where('album_id', '=', $album_cache_key)
			->where('user_id', $user_id)
			->first();

		if ($cached !== null) {
			return Thumb::createFromPhoto($cached->photo);
		}

		$thumbs = $compute_live();

		// photo_id is NOT NULL - only seed the cache when a photo actually qualifies.
		// An empty result is cheap to recompute and has nothing to cache anyway.
		if (count($thumbs) > 0) {
			AlbumUserThumb::query()->updateOrCreate(
				['album_id' => $album_cache_key, 'user_id' => $user_id],
				self::cacheColumns($thumbs)
			);
		}

		return $thumbs[0] ?? null;
	}

	/**
	 * The three photo columns of an `album_user_thumbs` row from an ordered
	 * thumb list, `null`-padded (Feature 075).
	 *
	 * @param non-empty-list<Thumb> $thumbs
	 *
	 * @return array{photo_id:string,photo_id_2:string|null,photo_id_3:string|null}
	 */
	public static function cacheColumns(array $thumbs): array
	{
		return [
			'photo_id' => $thumbs[0]->id,
			'photo_id_2' => isset($thumbs[1]) ? $thumbs[1]->id : null,
			'photo_id_3' => isset($thumbs[2]) ? $thumbs[2]->id : null,
		];
	}
}
