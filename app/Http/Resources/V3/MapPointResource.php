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
 * Response body of `GET /api/v3/Map/album` (Feature 086, FR-086-05):
 * every geotagged photo point of an album, Struct-of-Arrays per ADR-0009.
 * No clustering and no cap (ADR-086-01); only what the map header needs
 * to draw a dot and open the photo.
 */
#[TypeScript()]
class MapPointResource extends Data
{
	/**
	 * @param string[]        $ids
	 * @param (string|null)[] $album_ids  album to open each photo in (FR-086-06)
	 * @param float[]         $latitudes
	 * @param float[]         $longitudes
	 */
	public function __construct(
		public array $ids,
		public array $album_ids,
		public array $latitudes,
		public array $longitudes,
	) {
	}
}
