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

	/**
	 * From this zoom upward the viewport is small enough that grouping into
	 * grid cells hides photos the user can tell apart on screen: every photo
	 * is returned in `singleton_photos` and no bucket is built (Q-067-21).
	 */
	public const UNCLUSTERED_MIN_ZOOM = 14;

	/**
	 * Upper bound on the photos returned unclustered. A denser viewport falls
	 * back to the grid aggregation below, keeping NFR-067-01 intact.
	 */
	public const MAX_UNCLUSTERED_PHOTOS = 2000;

	protected function unclusteredPhotoCap(): int
	{
		return self::MAX_UNCLUSTERED_PHOTOS;
	}

	public function do(?AbstractAlbum $album, ?User $user, MapViewport $viewport, bool $include_sub_albums): MapBucketResource
	{
		$snapped = $viewport->snapToGrid();
		$cell = $snapped->cellSize();

		if ($viewport->zoom >= self::UNCLUSTERED_MIN_ZOOM) {
			$rows = $this->fetchUnclusteredRows($album, $user, $snapped, $include_sub_albums);
			if ($rows !== null) {
				return new MapBucketResource(
					bucket_ids: [],
					counts: [],
					centroid_latitudes: [],
					centroid_longitudes: [],
					singleton_photos: $this->buildMapPhotoResource($rows, $album, $user, $include_sub_albums),
				);
			}
		}

		$query = $this->buildScopedQuery($album, $user, $snapped, $include_sub_albums);

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
	 * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Photo>|\Illuminate\Database\Eloquent\Relations\Relation<\App\Models\Photo,AbstractAlbum&\Illuminate\Database\Eloquent\Model,mixed>
	 */
	private function buildScopedQuery(?AbstractAlbum $album, ?User $user, MapViewport $snapped, bool $include_sub_albums): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation
	{
		$query = $album === null ?
			$this->resolveRootQuery($user) :
			$this->resolveAlbumQuery($album, $include_sub_albums);

		$this->applyBoundingBoxFilter($query, $snapped);

		return $query;
	}

	/**
	 * Every distinct photo in `$snapped`, or null when there are more than
	 * {@see self::unclusteredPhotoCap()} (one extra row is fetched to tell).
	 *
	 * @return \stdClass[]|null
	 */
	private function fetchUnclusteredRows(?AbstractAlbum $album, ?User $user, MapViewport $snapped, bool $include_sub_albums): ?array
	{
		$cap = $this->unclusteredPhotoCap();
		$rows = $this->buildScopedQuery($album, $user, $snapped, $include_sub_albums)
			->select([])
			->selectRaw('photos.id as id, photos.title as title, photos.taken_at as taken_at, photos.latitude as latitude, photos.longitude as longitude')
			->distinct()
			->toBase()
			->limit($cap + 1)
			->get()
			->all();

		return count($rows) > $cap ? null : $rows;
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
