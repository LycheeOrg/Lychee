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
 * Response body of `GET /api/v3/Albums/{album_id}/Photos/buckets`.
 *
 * Struct-of-Arrays per ADR-0009: `bucket_ids`/`counts`/`labels` are
 * parallel, index-aligned arrays. `bucket_ids`/`counts` are grouped from the
 * materialized `photo_album.bucket_id` column (never a live date-truncation
 * function); `labels[i]` is a ready-to-render display string for
 * `bucket_ids[i]`, computed at read time (not materialized) so a client can
 * render sticky headers with zero client-side date formatting.
 * `bucketable` is `false` (all three arrays empty) when the album's own
 * effective photo-sort column is `OWNER_ID` — excluded from photo bucketing
 * entirely per explicit user direction, mirroring
 * {@see \App\Http\Resources\V3\AlbumBucketResource}'s own shape exactly.
 */
#[TypeScript()]
class PhotoBucketResource extends Data
{
	/**
	 * @param string[] $bucket_ids
	 * @param int[]    $counts
	 * @param string[] $labels
	 */
	public function __construct(
		public array $bucket_ids,
		public array $counts,
		public array $labels,
		public bool $bucketable,
	) {
	}
}
