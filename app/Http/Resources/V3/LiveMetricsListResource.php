<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\V3;

use App\Enum\MetricsAction;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Response body of `GET /api/v3/Metrics` (Feature 079).
 *
 * Struct-of-Arrays per ADR-0009, newest first. Each index is one group of
 * events sharing `(action, album_id, photo_id)` within the same minute:
 * `created_ats[i]` is the latest event of the group and `counts[i]` its size.
 * `thumb_photo_ids[i]` is resolved through
 * `GET /api/v3/Asset/{album_ids[i]}/{thumb_photo_ids[i]}/thumb`.
 * `is_truncated` is set when more groups exist than `live_metrics_result_limit`
 * (ADR-069-01, strategy 3).
 */
#[TypeScript()]
class LiveMetricsListResource extends Data
{
	/**
	 * @param string[]        $created_ats
	 * @param MetricsAction[] $actions
	 * @param string[]        $album_ids
	 * @param (string|null)[] $photo_ids
	 * @param string[]        $titles
	 * @param (string|null)[] $thumb_photo_ids
	 * @param int[]           $counts
	 */
	public function __construct(
		public array $created_ats,
		public array $actions,
		public array $album_ids,
		public array $photo_ids,
		public array $titles,
		public array $thumb_photo_ids,
		public array $counts,
		public bool $is_truncated,
	) {
	}
}
