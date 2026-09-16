<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\DTO;

use App\DTO\MapViewport;
use Tests\AbstractTestCase;

/**
 * Unit tests for {@see MapViewport} (T-067-01): `cellSizeForZoom()` against
 * the pinned formula (Q-067-13), and `snapToGrid()`'s idempotency/
 * outward-only behavior (NFR-067-04).
 */
class MapViewportTest extends AbstractTestCase
{
	public function testCellSizeForZoomMatchesPinnedFormula(): void
	{
		// Q-067-13 (amended): a 1/64th-tile at `$zoom`, i.e. one tile at `$zoom + 6`.
		self::assertSame(5.625, MapViewport::cellSizeForZoom(0));
		self::assertSame(2.8125, MapViewport::cellSizeForZoom(1));
		self::assertEqualsWithDelta(0.0054931640625, MapViewport::cellSizeForZoom(10), 1e-9);
	}

	public function testCellSizeForZoomIsMonotonicallyDecreasing(): void
	{
		$previous = MapViewport::cellSizeForZoom(0);
		for ($zoom = 1; $zoom <= 24; $zoom++) {
			$current = MapViewport::cellSizeForZoom($zoom);
			self::assertLessThan($previous, $current, "cell size at zoom {$zoom} must be smaller than at zoom " . ($zoom - 1));
			$previous = $current;
		}
	}

	public function testSnapToGridIsIdempotent(): void
	{
		$viewport = new MapViewport(north: 10.0, south: -5.0, east: 20.0, west: -15.0, zoom: 5);
		$snapped_once = $viewport->snapToGrid();
		$snapped_twice = $snapped_once->snapToGrid();

		self::assertSame($snapped_once->north, $snapped_twice->north);
		self::assertSame($snapped_once->south, $snapped_twice->south);
		self::assertSame($snapped_once->east, $snapped_twice->east);
		self::assertSame($snapped_once->west, $snapped_twice->west);
	}

	public function testSnapToGridIsOutwardOnly(): void
	{
		$viewport = new MapViewport(north: 10.3, south: -5.7, east: 20.9, west: -15.1, zoom: 5);
		$snapped = $viewport->snapToGrid();

		self::assertGreaterThanOrEqual($viewport->north, $snapped->north);
		self::assertLessThanOrEqual($viewport->south, $snapped->south);
		self::assertGreaterThanOrEqual($viewport->east, $snapped->east);
		self::assertLessThanOrEqual($viewport->west, $snapped->west);
	}

	public function testSnapToGridAlignsToCellBoundaries(): void
	{
		$cell = MapViewport::cellSizeForZoom(5);
		$viewport = new MapViewport(north: 10.3, south: -5.7, east: 20.9, west: -15.1, zoom: 5);
		$snapped = $viewport->snapToGrid();

		foreach ([$snapped->north, $snapped->south, $snapped->east, $snapped->west] as $bound) {
			$ratio = $bound / $cell;
			self::assertEqualsWithDelta(round($ratio), $ratio, 1e-6, 'snapped bound must land on a whole grid-cell multiple');
		}
	}

	public function testSnapToGridOfAlreadySnappedViewportIsNoOp(): void
	{
		$cell = MapViewport::cellSizeForZoom(4);
		$viewport = new MapViewport(north: 3 * $cell, south: -2 * $cell, east: 5 * $cell, west: -4 * $cell, zoom: 4);
		$snapped = $viewport->snapToGrid();

		self::assertEqualsWithDelta($viewport->north, $snapped->north, 1e-9);
		self::assertEqualsWithDelta($viewport->south, $snapped->south, 1e-9);
		self::assertEqualsWithDelta($viewport->east, $snapped->east, 1e-9);
		self::assertEqualsWithDelta($viewport->west, $snapped->west, 1e-9);
	}
}
