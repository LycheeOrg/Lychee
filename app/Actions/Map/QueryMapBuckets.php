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

/**
 * Query logic for `GET /api/v3/Map/buckets` (FR-067-05..08). Computes one
 * driver-portable SQL `GROUP BY FLOOR(latitude/$cell), FLOOR(longitude/$cell)`
 * + `COUNT(*)`/`AVG(latitude)`/`AVG(longitude)`, `toBase()`-only (no Eloquent
 * hydration) — cost bounded by the number of distinct grid cells
 * intersecting the snapped viewport, never by total geotagged-photo count
 * in scope (NFR-067-01).
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

		$rows = $query
			->select([])
			->selectRaw(
				'FLOOR(latitude / ?) as lat_cell, FLOOR(longitude / ?) as lng_cell, COUNT(*) as bucket_count, AVG(latitude) as avg_lat, AVG(longitude) as avg_lng',
				[$cell, $cell],
			)
			->groupBy('lat_cell', 'lng_cell')
			->toBase()
			->get();

		$bucket_ids = [];
		$counts = [];
		$centroid_latitudes = [];
		$centroid_longitudes = [];

		foreach ($rows as $row) {
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
		);
	}
}
