<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Assets\DbBool;
use App\Models\Photo;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Feature 082 (FR-082-06): where a partial 360° photo sits in its full
 * panorama, in pixels of the original. Absent (null) for full spheres and
 * flat photos.
 */
#[TypeScript()]
class PanoramaResource extends Data
{
	public function __construct(
		public int $full_width,
		public int $full_height,
		public int $crop_left,
		public int $crop_top,
	) {
	}

	public static function fromPhoto(Photo $photo): ?self
	{
		return self::fromColumns($photo->is_360, $photo->pano_full_width, $photo->pano_full_height, $photo->pano_crop_left, $photo->pano_crop_top);
	}

	/**
	 * Builds the resource from raw column values (Eloquent attributes or
	 * `toBase()` rows, whose numbers may come back as strings).
	 */
	public static function fromColumns(mixed $is_360, mixed $full_width, mixed $full_height, mixed $crop_left, mixed $crop_top): ?self
	{
		if (!DbBool::parse($is_360) || $full_width === null || $full_height === null || $crop_left === null || $crop_top === null) {
			return null;
		}

		return new self((int) $full_width, (int) $full_height, (int) $crop_left, (int) $crop_top);
	}
}
