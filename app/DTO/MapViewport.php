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
	 * A 1/64th-tile-width in decimal degrees at Web-Mercator zoom `$zoom`
	 * (Q-067-13, amended twice - one map tile, then a quarter-tile, both
	 * still read as a whole neighborhood/town at typical browsing zooms:
	 * a bucket several km wide lumped together photos that are clearly
	 * separate on screen, and because cell *area* quarters every zoom
	 * level, a cluster sitting just above `LEAF_THRESHOLD` had every one
	 * of its sub-cells drop below threshold in the same zoom step -
	 * looking like the cluster vanished, replaced instantly by a scatter
	 * of individual markers). `GRID_ZOOM_OFFSET` clusters six extra zoom
	 * levels finer than `$zoom` itself instead, still halving once per
	 * zoom level and still tied to Leaflet's own tile grid (just a deeper
	 * level of it), so `snapToGrid()`'s boundary math is unaffected. Zoom
	 * 0 -> 5.625°; zoom 11 -> ~0.0027° (~300 m, block-sized rather than
	 * the ~5 km town-sized cell the previous constant gave at that zoom);
	 * zoom 18 -> ~0.0000215° (~2.4 m).
	 */
	private const GRID_ZOOM_OFFSET = 6;

	public static function cellSizeForZoom(int $zoom): float
	{
		return 360.0 / (2 ** ($zoom + self::GRID_ZOOM_OFFSET));
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

		// `west > east` is this DTO's antimeridian-crossing convention (see
		// `ResolvesMapPhotoSource`'s own `$snapped->west > $snapped->east`
		// branch). Snapping each bound independently can collapse both onto
		// the same grid line at a large enough cell size (e.g. west=170,
		// east=-170 both snap to 0 at zoom 0/1), silently destroying that
		// marker and turning a whole-world crossing viewport into a
		// single-longitude one.
		$is_crossing_antimeridian = $this->west > $this->east;

		$snapped_east = self::snapUp($this->east, $cell);
		$snapped_west = self::snapDown($this->west, $cell);

		if ($is_crossing_antimeridian && $snapped_west <= $snapped_east) {
			// The crossing marker didn't survive snapping: the true snapped
			// range at this cell size is the entire world, so represent it
			// as such explicitly rather than as a degenerate point/range.
			$snapped_west = -180.0;
			$snapped_east = 180.0;
		}

		return new self(
			north: self::snapUp($this->north, $cell),
			south: self::snapDown($this->south, $cell),
			east: $snapped_east,
			west: $snapped_west,
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
