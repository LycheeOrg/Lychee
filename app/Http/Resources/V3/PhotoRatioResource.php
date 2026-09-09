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
 * Response body of `GET /api/v3/Albums/{album_id}/Photos`. Struct-of-Arrays
 * per ADR-0009: every array is index-aligned to `ids`, whole-album-at-once,
 * never paginated.
 *
 * Conditionally-present fields (`rating_avgs`/`rating_users`/`thumb_infos`/
 * `tags`) are typed `array|Optional` — `Optional::create()` omits the key
 * from the JSON payload entirely (never a null-filled array), mirroring
 * {@see \App\Http\Resources\V3\AlbumRightsResource}'s own `Optional`
 * convention. Gates are evaluated once per request (uniform for every row),
 * not per photo.
 */
#[TypeScript()]
class PhotoRatioResource extends Data
{
	/**
	 * @param string[]                 $ids
	 * @param string[]                 $titles            guest-blanked identically to {@see \App\Http\Resources\Models\PhotoResource::$title}'s `file_name_hidden` rule
	 * @param string[]                 $types             raw photo `type` enum value
	 * @param string[]                 $bucket_ids        `"unknown"` for a `null` `photo_album.bucket_id`
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
		public array $titles,
		public array $types,
		public array $bucket_ids,
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
		public array|Optional $rating_avgs,
		public array|Optional $rating_users,
		public array|Optional $thumb_infos,
		public array|Optional $tags,
	) {
	}
}
