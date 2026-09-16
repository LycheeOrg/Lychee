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
 * Response body of `GET /api/v3/Map/Photos` (FR-067-09). Struct-of-Arrays
 * per ADR-0009, populated **only** when the viewport's total photo count is
 * `<=` {@see \App\Actions\Map\QueryMapPhotos::MAX_VIEWPORT_PHOTOS} (Q-067-13,
 * amended - every distinct photo in the viewport, not a per-grid-cell
 * subset); empty above that cap, in favor of `/Map/buckets`' aggregate count
 * badges. Purpose-built, not a reuse of `PhotoResource` (FR-067-11) - no
 * `tags`/`rating`/`statistics`/`palette`/`size_variants` and no URL field at
 * all: marker imagery is fetched by the frontend directly from the existing
 * v3 Asset endpoint, keyed on `ids[]`/`album_ids[]` (Q-067-12).
 */
#[TypeScript()]
class MapPhotoResource extends Data
{
	/**
	 * @param string[]        $ids
	 * @param (string|null)[] $album_ids  each photo's own real, viewer-accessible containing album id (Q-067-15) - never the request's/scope's own `album_id`; `null` for a photo with no resolvable containing album (e.g. unsorted, root scope)
	 * @param string[]        $titles     guest-blanked identically to {@see \App\Http\Resources\Models\PhotoResource::$title}'s `file_name_hidden` rule
	 * @param (string|null)[] $taken_ats  preformatted via `date_format_sidebar_taken_at`, native PHP `date()`/`DateTime` against the raw column string (NFR-067-02) - never Carbon
	 * @param float[]         $latitudes
	 * @param float[]         $longitudes
	 */
	public function __construct(
		public array $ids,
		public array $album_ids,
		public array $titles,
		public array $taken_ats,
		public array $latitudes,
		public array $longitudes,
	) {
	}
}
