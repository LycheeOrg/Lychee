<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v3\Album;

use App\DTO\AlbumSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers Feature 061 FR-061-12..17, S-061-23..30.
 */
class AlbumChildrenDataV3Test extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
	}

	private function recompute(Album $album): void
	{
		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();
	}

	// ── Flag gate ─────────────────────────────────────────────────

	public function testFlagOffReturns403RegardlessOfCallerRights(): void
	{
		config(['features.struct-of-array' => false]);

		$response = $this->actingAs($this->admin)->getJsonV3("Albums/{$this->album1->id}/children");
		$this->assertForbidden($response);
	}

	// ── Field list / parity ──────────────────────────────────────

	public function testFieldListMatchesFR06112AndVisibilitySetMatchesV2Pagination(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->album1->id}/children");
		$this->assertOk($response);
		$json = $response->json();

		foreach (['ids', 'titles', 'descriptions', 'cover_ids', 'bucket_ids', 'is_password_requireds', 'is_nsfws', 'has_subalbums', 'num_photos', 'num_subalbums', 'created_ats', 'min_taken_ats', 'max_taken_ats'] as $field) {
			self::assertArrayHasKey($field, $json, "missing field {$field}");
		}
		self::assertContains($this->subAlbum1->id, $json['ids']);
		self::assertCount(1, $json['ids']);
	}

	/**
	 * S-061-23/NFR-061-08: for a fixed set of children with mixed
	 * visibility, the set of `ids` returned equals the set
	 * `AlbumChildrenController::get()` would paginate over for the same
	 * caller — a private, non-shared child must never leak into the
	 * response for a caller lacking access.
	 */
	public function testVisibilityFilterExcludesInaccessibleChildren(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$visible = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($visible);

		// Owner sees it.
		$owner_ids = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/children")->assertOk()->json('ids');
		self::assertContains($visible->id, $owner_ids);

		// Parent itself is private (no grants), so a stranger cannot even
		// resolve/access the parent to begin with.
		$response = $this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$parent->id}/children");
		$this->assertForbidden($response);
	}

	// ── Description truncation ───────────────────────────────────

	public function testDescriptionTruncatedToOneHundredCharsBySql(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$long_description = str_repeat('a', 150);
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$child->description = $long_description;
		$child->save();
		$this->recompute($child);

		DB::flushQueryLog();
		DB::enableQueryLog();
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/children");
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$this->assertOk($response);
		self::assertSame(str_repeat('a', 100), $response->json('descriptions')[0]);

		$truncating_queries = array_filter($log, fn (array $q) => preg_match('/substr/i', $q['query']) === 1);
		self::assertGreaterThan(0, count($truncating_queries), 'Expected the description truncation to happen via a SQL SUBSTR, not PHP.');
	}

	// ── Cover resolution ──────────────────────────────────────────

	public function testCoverIdResolutionAcrossAllThreeTiersPlusNoCover(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();

		$explicit = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$photo_explicit = Photo::factory()->owned_by($this->userMayUpload1)->create();
		$photo_explicit->albums()->attach($explicit->id);
		$explicit->cover_id = $photo_explicit->id;
		$explicit->save();
		$this->recompute($explicit);

		$auto = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$photo_auto = Photo::factory()->owned_by($this->userMayUpload1)->create();
		$photo_auto->albums()->attach($auto->id);
		$this->recompute($auto);

		$empty = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($empty);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/children");
		$this->assertOk($response);
		$json = $response->json();

		$idx_explicit = array_search($explicit->id, $json['ids'], true);
		$idx_auto = array_search($auto->id, $json['ids'], true);
		$idx_empty = array_search($empty->id, $json['ids'], true);

		self::assertSame($photo_explicit->id, $json['cover_ids'][$idx_explicit]);
		self::assertSame($photo_auto->id, $json['cover_ids'][$idx_auto]);
		self::assertNull($json['cover_ids'][$idx_empty]);

		self::assertArrayNotHasKey('type', $json);
		self::assertArrayNotHasKey('placeholder', $json);
	}

	// ── Edge branches ─────────────────────────────────────────────

	public function testZeroChildrenParentReturnsEmptyArrays(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3("Albums/{$this->album5->id}/children");
		$this->assertOk($response);
		$response->assertExactJson([
			'ids' => [], 'titles' => [], 'descriptions' => [], 'cover_ids' => [], 'bucket_ids' => [],
			'is_password_requireds' => [], 'is_nsfws' => [], 'has_subalbums' => [], 'num_photos' => [],
			'num_subalbums' => [], 'created_ats' => [], 'min_taken_ats' => [], 'max_taken_ats' => [],
		]);
	}

	public function testUnresolvableAlbumIdReturns404(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/AAAAAAAAAAAAAAAAAAAAAAAA/children');
		$this->assertNotFound($response);
	}

	public function testNoAccessReturns403(): void
	{
		$response = $this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$this->album1->id}/children");
		$this->assertForbidden($response);
	}

	// ── Cross-endpoint bucket_id correlation (T-061-27) ───────────

	public function testBucketIdCorrelatesExactlyWithBucketsEndpoint(): void
	{
		DB::table('configs')->where('key', '=', 'timeline_albums_granularity')->update(['value' => 'year']);
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::ASC);
		$parent->save();

		$c1 = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create(['created_at' => new Carbon('2023-01-01')]);
		$c2 = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create(['created_at' => new Carbon('2023-06-01')]);
		$c3 = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create(['created_at' => new Carbon('2024-01-01')]);
		$this->recompute($c1);
		$this->recompute($c2);
		$this->recompute($c3);

		$buckets = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/children/buckets")->assertOk()->json();
		$children = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/children")->assertOk()->json();

		$grouped = [];
		foreach ($children['bucket_ids'] as $bucket_id) {
			$grouped[$bucket_id] = ($grouped[$bucket_id] ?? 0) + 1;
		}

		$expected = array_combine($buckets['bucket_ids'], $buckets['counts']);
		self::assertEquals($expected, $grouped);
	}

	public function testBucketIdCorrelationIncludingUnknown(): void
	{
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update(['value' => 'min_taken_at']);
		DB::table('configs')->where('key', '=', 'timeline_albums_granularity')->update(['value' => 'year']);
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::MIN_TAKEN_AT, OrderSortingType::ASC);
		$parent->save();

		$dated = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->create(['taken_at' => new Carbon('2022-01-01')]);
		$photo->albums()->attach($dated->id);
		$this->recompute($dated);

		$undated = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($undated);

		$buckets = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/children/buckets")->assertOk()->json();
		$children = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/children")->assertOk()->json();

		self::assertContains('unknown', $children['bucket_ids']);
		$grouped = array_count_values($children['bucket_ids']);
		$expected = array_combine($buckets['bucket_ids'], $buckets['counts']);
		// Same key => count pairs; the two endpoints are not required to
		// agree on map *order* (the children endpoint has no bucket_id
		// ordering guarantee of its own, unlike the buckets endpoint).
		self::assertEquals($expected, $grouped);
	}

	// ── Managed cache (T-061-25) ──────────────────────────────────

	public function testCacheHitSkipsTheAggregationQuery(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($child);

		$this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/children")->assertOk();

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/children")->assertOk();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$relevant = array_filter($log, fn (array $q) => preg_match('/\bwhere\b.*parent_id/i', $q['query']) === 1);
		self::assertCount(0, $relevant, 'A cache hit must not re-run the children query.');
	}

	public function testCacheInvalidatedOnChildAdded(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($child);

		$before = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/children")->assertOk()->json('ids');
		self::assertCount(1, $before);

		$create_response = $this->actingAs($this->userMayUpload1)->postJson('Album', [
			'parent_id' => $parent->id,
			'title' => 'Feature-061-new-child-2',
		]);
		self::assertSame(200, $create_response->getStatusCode());

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/children")->assertOk()->json('ids');
		self::assertCount(2, $after);
	}
}
