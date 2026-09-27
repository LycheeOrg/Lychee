<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\V3;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Response body of `GET /api/v3/Map/buckets` (FR-067-05). Struct-of-Arrays
 * per ADR-0009: `bucket_ids`/`counts`/`centroid_latitudes`/
 * `centroid_longitudes` are parallel, index-aligned arrays - one entry per
 * distinct grid cell with at least two photos intersecting the (snapped)
 * viewport, never per photo. A cell holding a single photo is returned in
 * `singleton_photos` instead, so it renders as a photo point rather than a
 * `1` badge (FR-067-25, Q-067-20).
 */
#[TypeScript()]
class MapBucketResource extends Data
{
	/**
	 * @param string[]         $bucket_ids          opaque `"{lat_cell}:{lng_cell}"` grid-cell identifiers
	 * @param int[]            $counts              photo count for that cell
	 * @param float[]          $centroid_latitudes  average latitude of that cell's photos
	 * @param float[]          $centroid_longitudes average longitude of that cell's photos
	 * @param MapPhotoResource $singleton_photos    the photo of every cell holding exactly one photo
	 */
	public function __construct(
		public array $bucket_ids,
		public array $counts,
		public array $centroid_latitudes,
		public array $centroid_longitudes,
		public MapPhotoResource $singleton_photos,
	) {
	}
}
