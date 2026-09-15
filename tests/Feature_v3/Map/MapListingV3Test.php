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

namespace Tests\Feature_v3\Map;

use App\Events\MapListingCacheFlushRequested;
use App\Events\PhotoSaved;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function Safe\preg_match;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers `GET /api/v3/Map/buckets`, `/Map/Photos`, `/Map/tracks`
 * end-to-end: routing, request validation (S-067-13), gating (S-067-03),
 * and basic scope wiring. Builds its own isolated fixture per test, mirrors
 * `PhotoBucketsV3Test`'s own convention.
 */
class MapListingV3Test extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
		Configs::set('map_display', '1');
		Configs::set('map_display_public', '1');
	}

	private function defaultViewportParams(): array
	{
		return ['north' => 45.0, 'south' => 0.0, 'east' => 45.0, 'west' => 0.0, 'zoom' => 4];
	}

	// ── Flag gate ─────────────────────────────────────────────────

	public function testFlagOffReturns403OnBuckets(): void
	{
		config(['features.struct-of-array' => false]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/buckets', $this->defaultViewportParams());
		$this->assertForbidden($response);
	}

	public function testFlagOffReturns403OnPhotos(): void
	{
		config(['features.struct-of-array' => false]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/Photos', $this->defaultViewportParams());
		$this->assertForbidden($response);
	}

	// ── Validation (S-067-13) ───────────────────────────────────────

	public function testMissingViewportParamReturns422OnBuckets(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/buckets', ['south' => 0.0, 'east' => 45.0, 'west' => 0.0, 'zoom' => 4]);
		$this->assertUnprocessable($response);
	}

	public function testOutOfRangeZoomReturns422(): void
	{
		$params = $this->defaultViewportParams();
		$params['zoom'] = 99;
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/buckets', $params);
		$this->assertUnprocessable($response);
	}

	public function testMissingAlbumIdReturns422OnTracks(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/tracks');
		$this->assertUnprocessable($response);
	}

	// ── Root scope, success path ────────────────────────────────────

	public function testRootScopeBucketsReturnsGeotaggedPhoto(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.0', 'longitude' => '10.0']);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/buckets', $this->defaultViewportParams());
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['0:0'], 'counts' => [1]]);
	}

	public function testRootScopePhotosReturnsLeafPhoto(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.0', 'longitude' => '10.0']);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/Photos', $this->defaultViewportParams());
		$this->assertOk($response);
		$response->assertJson(['ids' => [$photo->id], 'album_ids' => [$album->id]]);
	}

	// ── Album scope, gating (S-067-03) ──────────────────────────────

	public function testUnauthorizedAlbumScopeReturns403(): void
	{
		$params = array_merge($this->defaultViewportParams(), ['album_id' => $this->album1->id]);
		$response = $this->actingAs($this->userNoUpload)->getJsonV3('Map/buckets', $params);
		$this->assertForbidden($response);
	}

	public function testAuthorizedAlbumScopeSucceeds(): void
	{
		$params = array_merge($this->defaultViewportParams(), ['album_id' => $this->album1->id]);
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/buckets', $params);
		$this->assertOk($response);
	}

	public function testUnauthorizedAlbumScopeTracksReturns403(): void
	{
		$response = $this->actingAs($this->userNoUpload)->getJsonV3('Map/tracks', ['album_id' => $this->album1->id]);
		$this->assertForbidden($response);
	}

	// ── map_display gating ──────────────────────────────────────────

	public function testMapDisplayDisabledReturns403(): void
	{
		Configs::set('map_display', '0');

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/buckets', $this->defaultViewportParams());
		$this->assertForbidden($response);
	}

	// ── Tracks endpoint (viewport-independent, FR-067-13) ───────────

	public function testTracksEndpointIgnoresViewportParams(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/tracks', array_merge(
			$this->defaultViewportParams(),
			['album_id' => $this->album1->id],
		));
		$this->assertOk($response);
		self::assertSame([], $response->json());
	}

	// ── Managed cache (I6) ───────────────────────────────────────────

	public function testCacheHitSkipsTheAggregationQuery(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.0', 'longitude' => '10.0']);

		$this->actingAs($this->userMayUpload1)->getJsonV3('Map/buckets', $this->defaultViewportParams())->assertOk();

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->actingAs($this->userMayUpload1)->getJsonV3('Map/buckets', $this->defaultViewportParams())->assertOk();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$aggregation_queries = array_filter($log, fn (array $q) => preg_match('/group by/i', $q['query']) === 1);
		self::assertCount(0, $aggregation_queries, 'A cache hit must not re-run the GROUP BY aggregation query.');
	}

	public function testCacheInvalidatedOnPhotoUploadedIntoAlbum(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.0', 'longitude' => '10.0']);

		$before = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/buckets', $this->defaultViewportParams())->assertOk()->json('counts');
		self::assertSame([1], $before);

		$new_photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.0', 'longitude' => '10.0']);
		PhotoSaved::dispatch([$new_photo->id]);

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/buckets', $this->defaultViewportParams())->assertOk()->json('counts');
		self::assertSame([2], $after);
	}

	public function testConfigChangeFlushesWarmCache(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.0', 'longitude' => '10.0']);

		$before = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/buckets', $this->defaultViewportParams())->assertOk()->json('counts');
		self::assertSame([1], $before);

		// A second geotagged photo, added without dispatching PhotoSaved -
		// the warm cache entry above must stay stale until a config-change
		// flush is dispatched (FR-067-24).
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '10.0', 'longitude' => '10.0']);

		$still_stale = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/buckets', $this->defaultViewportParams())->assertOk()->json('counts');
		self::assertSame([1], $still_stale, 'cache must still be warm/stale before the flush event');

		MapListingCacheFlushRequested::dispatch();

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/buckets', $this->defaultViewportParams())->assertOk()->json('counts');
		self::assertSame([2], $after);
	}

	/**
	 * CWE-200 regression: `QueryMapPhotos` blanks `titles[]` for guests only
	 * when it *computes* a response - the map-photo cache key carries no
	 * `file_name_hidden` dimension, so a warm guest-scoped entry cached
	 * while the setting was off must not keep leaking the real title to
	 * guests once an admin turns it on. Goes through the real
	 * `Settings::setConfigs` HTTP endpoint (not a direct event dispatch) to
	 * exercise the actual production wiring end-to-end.
	 */
	public function testTogglingFileNameHiddenFlushesWarmGuestScopedTitleCache(): void
	{
		// file_name_hidden is a level-1 (Supporter Edition) config - the real
		// Settings::setConfigs call below requires it.
		$this->requireSe();
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');
		Configs::set('file_name_hidden', '0');

		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->with_title('Secret Location')->create(['latitude' => '10.0', 'longitude' => '10.0']);
		AccessPermission::factory()->public()->visible()->for_album($album)->create();

		// Guest, warm cache: file_name_hidden is off, real title is visible.
		$before = $this->getJsonV3('Map/Photos', $this->defaultViewportParams())->assertOk()->json('titles');
		self::assertSame(['Secret Location'], $before);

		// Admin turns file_name_hidden on via the real Settings endpoint -
		// must flush the warm guest-scoped cache entry above.
		$this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [
				['key' => 'file_name_hidden', 'value' => '1'],
			],
		])->assertOk();
		// actingAs() persists across subsequent calls on this test instance -
		// log back out so the next request is genuinely unauthenticated
		// again, not still running as the admin (who always sees titles
		// regardless of file_name_hidden).
		Auth::logout();

		// Same guest, same viewport: must no longer see the real title.
		$after = $this->getJsonV3('Map/Photos', $this->defaultViewportParams())->assertOk()->json('titles');
		self::assertSame([''], $after, 'a warm guest-scoped cache entry must not keep leaking the real title after file_name_hidden is turned on');
		self::assertNotSame($photo->title, $after[0]);
	}
}
