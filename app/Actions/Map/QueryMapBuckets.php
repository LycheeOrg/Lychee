<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Map;

use App\Contracts\Models\AbstractAlbum;
use App\DTO\MapViewport;
use App\Http\Resources\V3\MapBucketResource;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Query logic for `GET /api/v3/Map/buckets` (FR-067-05..08). Computes one
 * driver-portable SQL `GROUP BY FLOOR(latitude/$cell), FLOOR(longitude/$cell)`
 * + `COUNT(*)`/`AVG(latitude)`/`AVG(longitude)`, `toBase()`-only (no Eloquent
 * hydration) — cost bounded by the number of distinct grid cells
 * intersecting the snapped viewport, never by total geotagged-photo count
 * in scope (NFR-067-01).
 *
 * A cell holding exactly one photo is not returned as a bucket: its photo
 * (the cell's `MIN(id)`) is fetched in one extra `whereIn` pass, bounded by
 * the cell count, and returned as `singleton_photos` (FR-067-25, Q-067-20).
 */
class QueryMapBuckets
{
	use ResolvesMapPhotoSource;

	public function do(?AbstractAlbum $album, ?User $user, MapViewport $viewport, bool $include_sub_albums): MapBucketResource
	{
		$snapped = $viewport->snapToGrid();
		$cell = $snapped->cellSize();

		$query = $album === null ?
			$this->resolveRootQuery($user) :
			$this->resolveAlbumQuery($album, $include_sub_albums);

		$this->applyBoundingBoxFilter($query, $snapped);

		// A photo linked into more than one album within scope (root, or
		// an `include_sub_albums` subtree) fans out into one row per
		// membership via the underlying query's own `LEFT JOIN albums`
		// (see resolveDistinctPhotoRows()'s own doc comment) - collapse to
		// one row per photo *before* aggregating, or such a photo would be
		// double-counted in both its bucket's count and centroid.
		$distinct_photos = $this->resolveDistinctPhotoRows($query);

		$rows = DB::query()
			->fromSub($distinct_photos, 'distinct_photos')
			->selectRaw(
				'FLOOR(latitude / ?) as lat_cell, FLOOR(longitude / ?) as lng_cell, COUNT(*) as bucket_count, AVG(latitude) as avg_lat, AVG(longitude) as avg_lng, MIN(id) as min_id',
				[$cell, $cell],
			)
			->groupBy('lat_cell', 'lng_cell')
			->get();

		$bucket_ids = [];
		$counts = [];
		$centroid_latitudes = [];
		$centroid_longitudes = [];
		$singleton_photo_ids = [];

		foreach ($rows as $row) {
			if ((int) $row->bucket_count === 1) {
				$singleton_photo_ids[] = (string) $row->min_id;
				continue;
			}

			$lat_cell = (int) round((float) $row->lat_cell);
			$lng_cell = (int) round((float) $row->lng_cell);

			$bucket_ids[] = "{$lat_cell}:{$lng_cell}";
			$counts[] = (int) $row->bucket_count;
			$centroid_latitudes[] = (float) $row->avg_lat;
			$centroid_longitudes[] = (float) $row->avg_lng;
		}

		return new MapBucketResource(
			bucket_ids: $bucket_ids,
			counts: $counts,
			centroid_latitudes: $centroid_latitudes,
			centroid_longitudes: $centroid_longitudes,
			singleton_photos: $this->buildMapPhotoResource(
				$this->fetchSingletonPhotoRows($singleton_photo_ids),
				$album,
				$user,
				$include_sub_albums,
			),
		);
	}

	/**
	 * `$photo_ids` already passed the scope/visibility filters in the
	 * aggregate query above, so a plain lookup by id is enough.
	 *
	 * @param string[] $photo_ids
	 *
	 * @return \stdClass[]
	 */
	private function fetchSingletonPhotoRows(array $photo_ids): array
	{
		if (count($photo_ids) === 0) {
			return [];
		}

		return DB::table('photos')
			->whereIn('id', $photo_ids)
			->select(['id', 'title', 'taken_at', 'latitude', 'longitude'])
			->get()
			->all();
	}
}
