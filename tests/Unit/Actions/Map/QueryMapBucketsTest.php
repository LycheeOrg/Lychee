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
 * bound (distinct cells, not photo count). T-067-38: single-photo cells
 * leave the bucket arrays for `singleton_photos` (FR-067-25, Q-067-20).
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
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.05', 'longitude' => '10.05']);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '40.0', 'longitude' => '40.0']);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '40.05', 'longitude' => '40.05']);

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 45.0, south: 0.0, east: 45.0, west: 0.0, zoom: 4);
		$resource = app(QueryMapBuckets::class)->do(null, $this->userMayUpload1, $viewport, false);

		$cell = MapViewport::cellSizeForZoom(4);
		$first_cell = ((int) floor(10.0 / $cell)) . ':' . ((int) floor(10.0 / $cell));
		$second_cell = ((int) floor(40.0 / $cell)) . ':' . ((int) floor(40.0 / $cell));

		$map = $this->toMap($resource);
		self::assertArrayHasKey($first_cell, $map);
		self::assertArrayHasKey($second_cell, $map);
		self::assertSame(2, $map[$first_cell]['count']);
		self::assertSame(2, $map[$second_cell]['count']);
		self::assertEqualsWithDelta(10.025, $map[$first_cell]['lat'], 1e-6);
		self::assertEqualsWithDelta(10.025, $map[$first_cell]['lng'], 1e-6);
		self::assertEqualsWithDelta(40.025, $map[$second_cell]['lat'], 1e-6);
		self::assertEqualsWithDelta(40.025, $map[$second_cell]['lng'], 1e-6);
		self::assertSame([], $resource->singleton_photos->ids);
	}

	/**
	 * FR-067-25 / S-067-23: a cell holding exactly one photo is not a
	 * bucket - its photo comes back in `singleton_photos`, with the same
	 * fields `QueryMapPhotos` returns for it.
	 */
	public function testSinglePhotoCellIsReturnedAsSingletonPhotoNotAsBucket(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.0', 'longitude' => '10.0']);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.05', 'longitude' => '10.05']);
		$single = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->with_title('Lonely')->create(['latitude' => '40.0', 'longitude' => '41.0']);

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 45.0, south: 0.0, east: 45.0, west: 0.0, zoom: 4);
		$resource = app(QueryMapBuckets::class)->do(null, $this->userMayUpload1, $viewport, false);

		$cell = MapViewport::cellSizeForZoom(4);
		$pair_cell = ((int) floor(10.0 / $cell)) . ':' . ((int) floor(10.0 / $cell));

		self::assertSame([$pair_cell], $resource->bucket_ids, 'the single-photo cell must not be returned as a bucket');
		self::assertSame([2], $resource->counts);

		$singletons = $resource->singleton_photos;
		self::assertSame([$single->id], $singletons->ids);
		self::assertSame([$album->id], $singletons->album_ids);
		self::assertSame(['Lonely'], $singletons->titles);
		self::assertCount(1, $singletons->taken_ats);
		self::assertEqualsWithDelta(40.0, $singletons->latitudes[0], 1e-6);
		self::assertEqualsWithDelta(41.0, $singletons->longitudes[0], 1e-6);
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

		self::assertSame([], $resource->bucket_ids, 'each side holds a single photo, so neither is a bucket');
		self::assertCount(2, $resource->singleton_photos->ids);
	}

	public function testNegativeLatLngFixtureIsBucketedCorrectly(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '-22.9', 'longitude' => '-43.2']);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '-22.9', 'longitude' => '-43.2']);

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 0.0, south: -45.0, east: 0.0, west: -90.0, zoom: 3);
		$resource = app(QueryMapBuckets::class)->do(null, $this->userMayUpload1, $viewport, false);

		$cell = MapViewport::cellSizeForZoom(3);
		$expected_bucket_id = ((int) floor(-22.9 / $cell)) . ':' . ((int) floor(-43.2 / $cell));

		self::assertSame([$expected_bucket_id], $resource->bucket_ids);
		self::assertSame([2], $resource->counts);
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
		self::assertSame([], $resource->singleton_photos->ids);
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

	/**
	 * Regression: root scope's `applySearchabilityFilter()` `LEFT JOIN`s
	 * `albums` unconditionally, so a photo linked into more than one album
	 * fans out into one row per membership - left uncollapsed, a plain
	 * `COUNT(*)` over that fanned-out row set double-counts such a photo.
	 */
	public function testRootScopePhotoInMultipleAlbumsIsCountedOnce(): void
	{
		$album_a = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$album_b = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album_a)->create(['latitude' => '10.0', 'longitude' => '10.0']);
		$photo->albums()->attach($album_b->id);

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 45.0, south: 0.0, east: 45.0, west: 0.0, zoom: 4);
		$resource = app(QueryMapBuckets::class)->do(null, $this->userMayUpload1, $viewport, false);

		self::assertSame([], $resource->bucket_ids, 'a photo linked into 2 albums must be counted once, not twice - a count of 2 would make it a bucket');
		self::assertSame([$photo->id], $resource->singleton_photos->ids);
	}

	/**
	 * Same regression, album scope with `include_sub_albums=true`: a photo
	 * linked into 2 different sub-albums within the requested subtree.
	 */
	public function testAlbumScopeWithSubAlbumsPhotoInMultipleSubAlbumsIsCountedOnce(): void
	{
		$root = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$sub_a = Album::factory()->children_of($root)->owned_by($this->userMayUpload1)->create();
		$sub_b = Album::factory()->children_of($root)->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($sub_a)->create(['latitude' => '10.0', 'longitude' => '10.0']);
		$photo->albums()->attach($sub_b->id);

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 45.0, south: 0.0, east: 45.0, west: 0.0, zoom: 4);
		$resource = app(QueryMapBuckets::class)->do($root, $this->userMayUpload1, $viewport, true);

		self::assertSame([], $resource->bucket_ids, 'a photo linked into 2 in-scope sub-albums must be counted once, not twice - a count of 2 would make it a bucket');
		self::assertSame([$photo->id], $resource->singleton_photos->ids);
		self::assertContains($resource->singleton_photos->album_ids[0], [$sub_a->id, $sub_b->id]);
	}

	/**
	 * Regression: `$album->all_photos()` (`HasManyPhotosRecursively`) bakes
	 * an `ORDER BY <effective sort column>` into its query as a side effect
	 * of resolving the relation, breaking the `GROUP BY` aggregate query
	 * under PostgreSQL ("column must appear in the GROUP BY clause or be
	 * used in an aggregate function") — sqlite silently tolerates the
	 * mismatch, so this inspects the generated SQL directly.
	 */
	public function testAlbumScopeWithSubAlbumsCarriesNoOrderByOnTheAggregateQuery(): void
	{
		$root = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$sub = Album::factory()->children_of($root)->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($sub)->create(['latitude' => '10.0', 'longitude' => '10.0']);

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 45.0, south: 0.0, east: 45.0, west: 0.0, zoom: 4);

		DB::flushQueryLog();
		DB::enableQueryLog();
		app(QueryMapBuckets::class)->do($root, $this->userMayUpload1, $viewport, true);
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		// The bug pattern specifically: an ORDER BY co-occurring with a GROUP BY
		// in the *same* query - PostgreSQL rejects an ORDER BY column that is
		// neither grouped nor aggregated.
		$grouped_and_ordered_queries = array_filter($log, function (array $q): bool {
			$sql = strtolower($q['query']);

			return str_contains($sql, 'group by') && str_contains($sql, 'order by');
		});
		self::assertSame([], array_values($grouped_and_ordered_queries), 'no GROUP BY aggregate query may also carry an ORDER BY on a non-grouped column');
	}

	/**
	 * Q-067-21: from `UNCLUSTERED_MIN_ZOOM` upward, photos of the same grid
	 * cell are not aggregated - every photo comes back in `singleton_photos`.
	 */
	public function testHighZoomReturnsEveryPhotoUnclustered(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$a = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.0', 'longitude' => '10.0']);
		$b = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.0000001', 'longitude' => '10.0000001']);
		$c = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.001', 'longitude' => '10.001']);

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 10.01, south: 9.99, east: 10.01, west: 9.99, zoom: QueryMapBuckets::UNCLUSTERED_MIN_ZOOM);
		$resource = app(QueryMapBuckets::class)->do(null, $this->userMayUpload1, $viewport, false);

		self::assertSame([], $resource->bucket_ids);
		self::assertSame([], $resource->counts);
		self::assertEqualsCanonicalizing([$a->id, $b->id, $c->id], $resource->singleton_photos->ids);
		self::assertSame([$album->id, $album->id, $album->id], $resource->singleton_photos->album_ids);
	}

	public function testBelowUnclusteredMinZoomStillClusters(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.0', 'longitude' => '10.0']);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.0000001', 'longitude' => '10.0000001']);

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 10.5, south: 9.5, east: 10.5, west: 9.5, zoom: QueryMapBuckets::UNCLUSTERED_MIN_ZOOM - 1);
		$resource = app(QueryMapBuckets::class)->do(null, $this->userMayUpload1, $viewport, false);

		self::assertCount(1, $resource->bucket_ids);
		self::assertSame([2], $resource->counts);
		self::assertSame([], $resource->singleton_photos->ids);
	}

	public function testHighZoomOverTheCapFallsBackToClusters(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.0', 'longitude' => '10.0']);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.0000001', 'longitude' => '10.0000001']);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.002', 'longitude' => '10.002']);

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 10.01, south: 9.99, east: 10.01, west: 9.99, zoom: QueryMapBuckets::UNCLUSTERED_MIN_ZOOM);
		$action = new class() extends QueryMapBuckets {
			protected function unclusteredPhotoCap(): int
			{
				return 2;
			}
		};
		$resource = $action->do(null, $this->userMayUpload1, $viewport, false);

		self::assertSame([2], $resource->counts);
		self::assertCount(1, $resource->singleton_photos->ids);
	}
}
