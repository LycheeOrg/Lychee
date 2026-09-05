<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\V3;

use App\Http\Resources\Models\ColourPaletteResource;
use App\Http\Resources\Models\PhotoStatisticsResource;
use App\Http\Resources\Models\SizeVariantsResouce;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Response body of `GET /api/v3/Albums/{album_id}/Photos/details`.
 * Struct-of-Arrays per ADR-0009, index-aligned to `ids` — except
 * `palette`/`size_variants`/`statistics`, deliberately **nested objects**
 * rather than flattened into dozens of parallel arrays (a scoped exception
 * to the SoA convention, justified because `details` is already the
 * bounded, richer-payload tier).
 *
 * `ratios` (tier 2) + `details` (this tier) combined must field-by-field
 * reconstruct every field of v2's `PhotoResource` for any photo a caller
 * can see, except `next_photo_id`/`previous_photo_id` (v2 itself never
 * populates them in this listing context either).
 *
 * Conditionally-present fields (EXIF-lite/GPS/location) are typed
 * `array|Optional`, gated once per request exactly like `PhotoResource`'s
 * `PreformattedPhotoData`/`PreComputedPhotoData` blocks already do.
 * `statistics` is the one genuinely **per-row** gate in this feature
 * (`metrics_access=owner`) — always present as a key, `null` per-element
 * where the gate denies that specific row.
 */
#[TypeScript()]
class PhotoDetailResource extends Data
{
	/**
	 * @param string[]                         $ids
	 * @param (string|null)[]                  $descriptions           raw, not Markdown-rendered
	 * @param string[][]                       $tags                   full tag-name list, always included
	 * @param (float|null)[]                   $rating_avgs
	 * @param string[]                         $licenses
	 * @param int[]                            $owner_ids
	 * @param (string|null)[]                  $nsfw_statuses
	 * @param string[]                         $checksums
	 * @param string[]                         $original_checksums
	 * @param string[]                         $updated_ats            raw ISO 8601, never Carbon-formatted
	 * @param (string|null)[]                  $live_photo_checksums
	 * @param (string|null)[]                  $live_photo_content_ids
	 * @param (string|null)[]                  $live_photo_urls
	 * @param int[]                            $face_counts
	 * @param (ColourPaletteResource|null)[]   $palette                nested
	 * @param (SizeVariantsResouce|null)[]     $size_variants          nested, all 9 variants
	 * @param (PhotoStatisticsResource|null)[] $statistics             nested; null per-row per `metrics_enabled` + per-row `metrics_access=owner`
	 * @param (string|null)[]|Optional         $makes                  gated by `display_exif_data`
	 * @param (string|null)[]|Optional         $models
	 * @param (string|null)[]|Optional         $lenses
	 * @param (string|null)[]|Optional         $apertures
	 * @param (string|null)[]|Optional         $shutters
	 * @param (string|null)[]|Optional         $focals
	 * @param (string|null)[]|Optional         $isos
	 * @param (float|null)[]|Optional          $latitudes              gated by `gps_coordinate_display`(+`_public` for guests)
	 * @param (float|null)[]|Optional          $longitudes
	 * @param (float|null)[]|Optional          $altitudes
	 * @param (string|null)[]|Optional         $locations              gated by `location_show`(+`_public` for guests)
	 */
	public function __construct(
		public array $ids,
		public array $descriptions,
		public array $tags,
		public array $rating_avgs,
		public array $licenses,
		public array $owner_ids,
		public array $nsfw_statuses,
		public array $checksums,
		public array $original_checksums,
		public array $updated_ats,
		public array $live_photo_checksums,
		public array $live_photo_content_ids,
		public array $live_photo_urls,
		public array $face_counts,
		public array $palette,
		public array $size_variants,
		public array $statistics,
		public array|Optional $makes,
		public array|Optional $models,
		public array|Optional $lenses,
		public array|Optional $apertures,
		public array|Optional $shutters,
		public array|Optional $focals,
		public array|Optional $isos,
		public array|Optional $latitudes,
		public array|Optional $longitudes,
		public array|Optional $altitudes,
		public array|Optional $locations,
	) {
	}
}
