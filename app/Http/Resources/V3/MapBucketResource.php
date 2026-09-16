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
 * distinct grid cell intersecting the (snapped) viewport, never per photo.
 */
#[TypeScript()]
class MapBucketResource extends Data
{
	/**
	 * @param string[] $bucket_ids          opaque `"{lat_cell}:{lng_cell}"` grid-cell identifiers
	 * @param int[]    $counts              photo count for that cell
	 * @param float[]  $centroid_latitudes  average latitude of that cell's photos
	 * @param float[]  $centroid_longitudes average longitude of that cell's photos
	 */
	public function __construct(
		public array $bucket_ids,
		public array $counts,
		public array $centroid_latitudes,
		public array $centroid_longitudes,
	) {
	}
}
