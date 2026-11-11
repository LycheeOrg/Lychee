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
 * Body of `GET /api/v3/Asset/...` and `GET /api/v3/Faces/{face_id}/photo/{variant}`
 * for a size variant stored on S3 (Feature 113, FR-113-01, ADR-113-01).
 *
 * `url` is the bucket URL (public bucket) or S3's presigned URL, which the
 * client renders with a plain `<img src>`: no XHR ever reaches the bucket,
 * so the bucket needs no CORS rule and never sees Lychee's request headers.
 */
#[TypeScript()]
class AssetUrlResource extends Data
{
	public function __construct(
		public string $url,
	) {
	}
}
