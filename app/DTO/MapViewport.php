<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

/**
 * Value object for a Map viewport bounding box + zoom level (Feature 067).
 * Shared by every Map query/request class: `GetMapBucketsRequest`/
 * `GetMapPhotosRequest` construct one from validated input,
 * {@see \App\Actions\Map\QueryMapBuckets}/{@see \App\Actions\Map\QueryMapPhotos}
 * consume it for both the SQL bounding-box filter and the cache key
 * (FR-067-01, FR-067-07).
 */
class MapViewport
{
	public function __construct(
		public readonly float $north,
		public readonly float $south,
		public readonly float $east,
		public readonly float $west,
		public readonly int $zoom,
	) {
	}

	/**
	 * One tile-width in decimal degrees at Web-Mercator zoom `$zoom`
	 * (Q-067-13) — halves exactly once per zoom level, ties grid cell
	 * boundaries to the same grid Leaflet's own tiles already use.
	 * Zoom 0 -> 360.0 (whole world, one cell); zoom 10 -> ~0.35°; zoom 18 ->
	 * ~0.0014°.
	 */
	public static function cellSizeForZoom(int $zoom): float
	{
		return 360.0 / (2 ** $zoom);
	}

	/**
	 * This viewport's own resolved grid cell size, per {@see self::cellSizeForZoom()}.
	 */
	public function cellSize(): float
	{
		return self::cellSizeForZoom($this->zoom);
	}

	/**
	 * Snaps the bounding box outward to whole grid cells at this viewport's
	 * own resolved cell size (Q-067-05, NFR-067-04) — before being used for
	 * both the SQL `WHERE`/`GROUP BY` and the cache key, so two overlapping
	 * but not identical raw viewports that fall inside the same snapped
	 * region produce byte-identical, cacheable requests. Idempotent:
	 * snapping an already-snapped viewport is a no-op.
	 *
	 * Values are rounded to 9 decimal places before `ceil()`/`floor()` to
	 * absorb ordinary floating-point representation noise (e.g.
	 * `35.15625000000001`) without ever snapping inward — 9 decimal places
	 * is many orders of magnitude finer than the smallest cell this DTO ever
	 * produces (`cellSizeForZoom(24)` ~= 2.146e-5).
	 */
	public function snapToGrid(): self
	{
		$cell = $this->cellSize();

		return new self(
			north: self::snapUp($this->north, $cell),
			south: self::snapDown($this->south, $cell),
			east: self::snapUp($this->east, $cell),
			west: self::snapDown($this->west, $cell),
			zoom: $this->zoom,
		);
	}

	private static function snapUp(float $value, float $cell): float
	{
		return ceil(round($value / $cell, 9)) * $cell;
	}

	private static function snapDown(float $value, float $cell): float
	{
		return floor(round($value / $cell, 9)) * $cell;
	}
}
