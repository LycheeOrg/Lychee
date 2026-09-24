<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\V3;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Response body of `GET /api/v3/Search/Photos` (Feature 069).
 *
 * Struct-of-Arrays per ADR-0009: every array is index-aligned to `ids`.
 * Deliberately close to {@see PhotoRatioResource}, with three differences,
 * each forced by search being a cross-album scope rather than one album's
 * contents:
 *
 * - **no `bucket_ids`** — search has no bucket tier at all (spec.md NG1,
 *   Q-069-02). Results are one flat, unbucketed list.
 * - **`album_ids`** — a per-row, viewer-accessible containing album id
 *   (FR-069-04). Load-bearing: it becomes the `{album_id}` path segment of the
 *   v3 Asset endpoint, without which a cross-album result cannot render a
 *   single thumbnail. Never null — a photo with no accessible containing album
 *   cannot appear in the result at all.
 * - **`is_truncated`** — whole-response, not per row. Unlike every other v3
 *   collection tier, search's scope has no structural ceiling (a two-character
 *   term can match a whole library), so it is bounded by the
 *   `search_result_limit` config and says so honestly when that bound bites
 *   (FR-069-02, ADR-0010's "capped with truncation" strategy).
 *
 * Exactly one row per distinct photo, regardless of how many albums it belongs
 * to (FR-069-05) — a deliberate, documented divergence from v2, which
 * double-counts such photos into its `total` (Q-069-08).
 *
 * Conditionally-present fields are typed `array|Optional`; `Optional::create()`
 * omits the key from the JSON payload entirely rather than emitting a
 * null-filled array. Every gate behind them is evaluated once per request, never
 * per photo (FR-069-14).
 */
#[TypeScript()]
class SearchPhotoResource extends Data
{
	/**
	 * @param string[]                 $ids
	 * @param string[]                 $album_ids         one accessible containing album per photo; never null
	 * @param string[]                 $titles            guest-blanked identically to {@see \App\Http\Resources\Models\PhotoResource::$title}'s `file_name_hidden` rule
	 * @param string[]                 $types             raw photo `type` enum value
	 * @param float[]                  $ratios            aspect ratio, `width/height`
	 * @param int[]                    $owner_ids
	 * @param bool[]                   $is_highlighteds
	 * @param bool[]                   $is_validateds
	 * @param bool[]                   $is_videos
	 * @param bool[]                   $is_raws
	 * @param bool[]                   $is_live_photos
	 * @param (string|null)[]          $taken_ats         raw ISO 8601, never Carbon-formatted
	 * @param string[]                 $created_ats       raw ISO 8601, never Carbon-formatted
	 * @param (string|null)[]          $taken_at_orig_tzs
	 * @param float[]|Optional         $rating_avgs       gated by `rating_enabled` + `PhotoPolicy::CAN_READ_RATINGS`
	 * @param (int|null)[]|Optional    $rating_users      the caller's own rating for that photo; gated the same as `rating_avgs`
	 * @param (string|null)[]|Optional $thumb_infos       description-mode only; gated by `display_thumb_photo_overlay!==never` AND `photo_thumb_info=description`
	 * @param string[][]|Optional      $tags              gated by `display_thumb_photo_overlay!==never` AND `photo_thumb_info=title` AND `photo_thumb_tags_enabled`
	 */
	public function __construct(
		public array $ids,
		public array $album_ids,
		public array $titles,
		public array $types,
		public array $ratios,
		public array $owner_ids,
		public array $is_highlighteds,
		public array $is_validateds,
		public array $is_videos,
		public array $is_raws,
		public array $is_live_photos,
		public array $taken_ats,
		public array $created_ats,
		public array $taken_at_orig_tzs,
		public bool $is_truncated,
		public array|Optional $rating_avgs,
		public array|Optional $rating_users,
		public array|Optional $thumb_infos,
		public array|Optional $tags,
	) {
	}
}
