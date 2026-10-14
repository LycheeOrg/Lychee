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
 * Response body of `GET /api/v3/Faces/queue` (Feature 093, API-093-03).
 *
 * Struct-of-Arrays per ADR-0009: one index per unassigned, non-dismissed
 * face the caller may name. Order: clustered faces grouped by cluster
 * (largest cluster first), confidence descending within a cluster, then
 * unclustered faces. Bounding box values are fractions (0..1) of the photo.
 * The photo itself is served by `GET /api/v3/Faces/{ids[i]}/photo/{variant}`
 * (ADR-093-01). `suggested_person_*[i]` is the person of the best suggestion
 * whose face is already named and visible to the caller, or null
 * (FR-093-12). `is_truncated` is set when more faces exist than
 * `ai_vision_face_queue_limit` (ADR-069-01, strategy 3).
 */
#[TypeScript()]
class FaceQueueResource extends Data
{
	/**
	 * @param string[]        $ids
	 * @param string[]        $photo_ids
	 * @param float[]         $xs
	 * @param float[]         $ys
	 * @param float[]         $widths
	 * @param float[]         $heights
	 * @param (string|null)[] $crop_urls
	 * @param (int|null)[]    $cluster_labels
	 * @param (string|null)[] $suggested_person_ids
	 * @param (string|null)[] $suggested_person_names
	 */
	public function __construct(
		public array $ids,
		public array $photo_ids,
		public array $xs,
		public array $ys,
		public array $widths,
		public array $heights,
		public array $crop_urls,
		public array $cluster_labels,
		public array $suggested_person_ids,
		public array $suggested_person_names,
		public bool $is_truncated,
	) {
	}
}
