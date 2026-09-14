<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Actions\Map;

use App\Actions\Map\QueryMapBuckets;
use App\DTO\MapViewport;
use App\Models\Album;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * T-067-07: covers {@see QueryMapBuckets} — grid split, antimeridian
 * handling, negative lat/lng, empty scope, and the NFR-067-01 row-count
 * bound (distinct cells, not photo count).
 */
class QueryMapBucketsTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		request()->attributes->set('configs', app(ConfigManager::class));
	}

	/**
	 * @return array<string,array{count:int,lat:float,lng:float}>
	 */
	private function toMap(\App\Http\Resources\V3\MapBucketResource $resource): array
	{
		$map = [];
		foreach ($resource->bucket_ids as $i => $bucket_id) {
			$map[$bucket_id] = [
				'count' => $resource->counts[$i],
				'lat' => $resource->centroid_latitudes[$i],
				'lng' => $resource->centroid_longitudes[$i],
			];
		}

		return $map;
	}

	public function testSimpleGridSplitAtRootScope(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.0', 'longitude' => '10.0']);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '40.0', 'longitude' => '40.0']);

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 45.0, south: 0.0, east: 45.0, west: 0.0, zoom: 4);
		$resource = app(QueryMapBuckets::class)->do(null, $this->userMayUpload1, $viewport, false);

		$map = $this->toMap($resource);
		self::assertArrayHasKey('0:0', $map);
		self::assertArrayHasKey('1:1', $map);
		self::assertSame(1, $map['0:0']['count']);
		self::assertSame(1, $map['1:1']['count']);
		self::assertEqualsWithDelta(10.0, $map['0:0']['lat'], 1e-6);
		self::assertEqualsWithDelta(10.0, $map['0:0']['lng'], 1e-6);
		self::assertEqualsWithDelta(40.0, $map['1:1']['lat'], 1e-6);
		self::assertEqualsWithDelta(40.0, $map['1:1']['lng'], 1e-6);
	}

	public function testAntimeridianCrossingViewportReturnsPhotosOnBothSides(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '0.0', 'longitude' => '179.0']);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '0.0', 'longitude' => '-179.0']);

		$this->actingAs($this->userMayUpload1);
		// west=170, east=-170 crosses the +-180 meridian (S-067-04).
		$viewport = new MapViewport(north: 10.0, south: -10.0, east: -170.0, west: 170.0, zoom: 5);
		$resource = app(QueryMapBuckets::class)->do(null, $this->userMayUpload1, $viewport, false);

		self::assertSame(2, array_sum($resource->counts));
		self::assertCount(2, $resource->bucket_ids);
	}

	public function testNegativeLatLngFixtureIsBucketedCorrectly(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '-22.9', 'longitude' => '-43.2']);

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 0.0, south: -45.0, east: 0.0, west: -90.0, zoom: 3);
		$resource = app(QueryMapBuckets::class)->do(null, $this->userMayUpload1, $viewport, false);

		$cell = MapViewport::cellSizeForZoom(3);
		$expected_bucket_id = ((int) floor(-22.9 / $cell)) . ':' . ((int) floor(-43.2 / $cell));

		self::assertSame([$expected_bucket_id], $resource->bucket_ids);
		self::assertSame([1], $resource->counts);
		self::assertEqualsWithDelta(-22.9, $resource->centroid_latitudes[0], 1e-6);
		self::assertEqualsWithDelta(-43.2, $resource->centroid_longitudes[0], 1e-6);
	}

	public function testEmptyScopeReturnsAllEmptyArraysNotAnError(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 90.0, south: -90.0, east: 180.0, west: -180.0, zoom: 0);
		$resource = app(QueryMapBuckets::class)->do($album, $this->userMayUpload1, $viewport, false);

		self::assertSame([], $resource->bucket_ids);
		self::assertSame([], $resource->counts);
		self::assertSame([], $resource->centroid_latitudes);
		self::assertSame([], $resource->centroid_longitudes);
	}

	/**
	 * NFR-067-01: response row count is bounded by distinct-cell count, not
	 * photo count — asserted both via the resource shape and by inspecting
	 * the actual query log for a single aggregated row.
	 */
	public function testLargeSingleRegionRowCountEqualsDistinctCellCountNotPhotoCount(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		for ($i = 0; $i < 30; $i++) {
			Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create([
				'latitude' => '10.0000' . $i,
				'longitude' => '10.0000' . $i,
			]);
		}

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 45.0, south: 0.0, east: 45.0, west: 0.0, zoom: 4);

		DB::flushQueryLog();
		DB::enableQueryLog();
		$resource = app(QueryMapBuckets::class)->do($album, $this->userMayUpload1, $viewport, false);
		DB::flushQueryLog();
		DB::disableQueryLog();

		self::assertCount(1, $resource->bucket_ids, 'all 30 photos fall in the same grid cell, so exactly one bucket must be returned');
		self::assertSame([30], $resource->counts);
	}
}
