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

namespace Tests\Feature_v3\Photo;

use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Jobs\RecomputeAlbumPhotoBucketsJob;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Services\TitleSplitter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers `GET /Albums/{album_id}/Photos/buckets`.
 *
 * Builds its own isolated fixture per test (rather than editing the shared
 * v2/v3 base fixture), mirroring `Tests\Feature_v3\Album\AlbumBucketsV3Test`.
 */
class PhotoBucketsV3Test extends BaseApiWithDataTest
{
	private function setInstanceDefaults(string $sorting_col = 'created_at', string $granularity = 'year'): void
	{
		DB::table('configs')->where('key', '=', 'sorting_photos_col')->update(['value' => $sorting_col]);
		DB::table('configs')->where('key', '=', 'timeline_photos_granularity')->update(['value' => $granularity]);
	}

	private function recompute(Album $album): void
	{
		(new RecomputeAlbumPhotoBucketsJob($album->id))->handle();
	}

	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
	}

	// ── Flag gate ─────────────────────────────────────────────────

	public function testFlagOffReturns403RegardlessOfCallerRights(): void
	{
		config(['features.struct-of-array' => false]);

		$response = $this->actingAs($this->admin)->getJsonV3("Albums/{$this->album1->id}/Photos/buckets");
		$this->assertForbidden($response);
	}

	// ── Grouping per sort column ──────────────────────────────────

	public function testGroupsByCreatedAt(): void
	{
		$this->setInstanceDefaults('created_at', 'year');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['created_at' => new Carbon('2023-05-01')]);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['created_at' => new Carbon('2024-05-01')]);
		$this->recompute($album);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['2023', '2024'], 'counts' => [1, 1], 'bucketable' => true]);
	}

	public function testGroupsByTakenAtWithUndatedPhotosUnderUnknown(): void
	{
		$this->setInstanceDefaults('taken_at', 'year');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['taken_at' => new Carbon('2022-06-15')]);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['taken_at' => null]);
		$this->recompute($album);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['2022', 'unknown'], 'counts' => [1, 1], 'bucketable' => true]);
	}

	public function testGroupsByTitleDatePrefixWithUnknown(): void
	{
		DB::table('configs')->where('key', '=', 'photo_title_bucket_mode')->update(['value' => 'date_prefix']);
		$this->setInstanceDefaults('title', 'year');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$p1 = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->with_title('2024 trip')->create();
		$p2 = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->with_title('no date here')->create();
		foreach ([$p1, $p2] as $p) {
			$p->title_base = TitleSplitter::split($p->title)->base;
			$p->save();
		}
		$this->recompute($album);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['2024', 'unknown'], 'counts' => [1, 1], 'bucketable' => true]);
	}

	public function testGroupsByTitleAlphabeticalUnaffectedByAlbumOnlyConfig(): void
	{
		// Feature 061's album-only title_bucket_mode is deliberately set to
		// the OPPOSITE mode here, to prove this endpoint never reads it.
		DB::table('configs')->where('key', '=', 'title_bucket_mode')->update(['value' => 'date_prefix']);
		DB::table('configs')->where('key', '=', 'photo_title_bucket_mode')->update(['value' => 'alphabetical']);
		DB::table('configs')->where('key', '=', 'photo_title_bucket_prefix_length')->update(['value' => '1']);
		$this->setInstanceDefaults('title', 'year');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->with_title('Zebras')->create();
		$photo->title_base = TitleSplitter::split($photo->title)->base;
		$photo->save();
		$this->recompute($album);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['z'], 'labels' => ['z']]);
	}

	public function testOwnerIdSortColumnIsNotBucketableAndRunsNoQuery(): void
	{
		$this->setInstanceDefaults('owner_id', 'year');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		Photo::factory()->owned_by($this->userMayUpload2)->in($album)->create();
		$this->recompute($album);

		DB::flushQueryLog();
		DB::enableQueryLog();
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$this->assertOk($response);
		$response->assertExactJson(['bucket_ids' => [], 'counts' => [], 'labels' => [], 'bucketable' => false]);

		$group_by_queries = array_filter($log, fn (array $q) => preg_match('/group by/i', $q['query']) === 1);
		$this->assertCount(0, $group_by_queries, 'OWNER_ID must short-circuit before any GROUP BY runs.');
	}

	public function testGroupsByIsHighlighted(): void
	{
		$this->setInstanceDefaults('is_highlighted', 'year');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_highlighted' => true]);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_highlighted' => false]);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_highlighted' => false]);
		$this->recompute($album);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['0', '1'], 'counts' => [2, 1], 'bucketable' => true]);
	}

	public function testGroupsByType(): void
	{
		$this->setInstanceDefaults('type', 'year');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['type' => 'image/jpeg']);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['type' => 'video/mp4']);
		$this->recompute($album);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['image/jpeg', 'video/mp4'], 'counts' => [1, 1], 'bucketable' => true]);
	}

	public function testGroupsByRatingAvgWithUnratedUnderUnknownAndZeroUnreachable(): void
	{
		$this->setInstanceDefaults('rating_avg', 'year');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['rating_avg' => '4.6000']);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['rating_avg' => null]);
		$this->recompute($album);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['5', 'unknown'], 'counts' => [1, 1], 'bucketable' => true]);
	}

	// ── Access / resolution edge cases ────────────────────────────

	public function testTagAlbumIdReturns404(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->tagAlbum1->id}/Photos/buckets");
		$this->assertNotFound($response);
	}

	public function testUnknownAlbumIdReturns404(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/AAAAAAAAAAAAAAAAAAAAAAAA/Photos/buckets');
		$this->assertNotFound($response);
	}

	public function testNoAccessReturns403(): void
	{
		$response = $this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$this->album1->id}/Photos/buckets");
		$this->assertForbidden($response);
	}

	public function testZeroPhotosAlbumReturnsEmptyArraysBucketableTrue(): void
	{
		$this->setInstanceDefaults('created_at', 'year');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($response);
		$response->assertExactJson(['bucket_ids' => [], 'counts' => [], 'labels' => [], 'bucketable' => true]);
	}

	// ── Upload-validation curation ────────────────────────────────

	public function testNonAdminBucketCountsExcludeOtherUsersUnvalidatedUploads(): void
	{
		$this->setInstanceDefaults('is_highlighted', 'year');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		// Uploader's own pending photo - visible to them, not to others.
		Photo::factory()->owned_by($this->userMayUpload2)->in($album)->create(['is_highlighted' => false, 'is_validated' => false]);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_highlighted' => false, 'is_validated' => true]);
		$this->recompute($album);

		// A different non-admin never sees the pending photo in the counts.
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['0'], 'counts' => [1], 'bucketable' => true]);

		// The admin sees both.
		$admin_response = $this->actingAs($this->admin)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($admin_response);
		$admin_response->assertJson(['bucket_ids' => ['0'], 'counts' => [2], 'bucketable' => true]);
	}

	// ── Regression: bucket_id is per-pivot-row, not per-photo ─────────────

	public function testSamePhotoTwoAlbumsDifferentSettingsShowsDivergentBucketsPerAlbum(): void
	{
		$this->setInstanceDefaults('created_at', 'year');
		$album_a = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$album_b = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$album_a->photo_sorting = new PhotoSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::ASC);
		$album_a->save();
		$album_b->photo_sorting = new PhotoSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::ASC);
		$album_b->photo_timeline = \App\Enum\TimelinePhotoGranularity::MONTH;
		$album_b->save();

		$photo = Photo::factory()->owned_by($this->userMayUpload1)->create(['created_at' => new Carbon('2022-06-15')]);
		DB::table('photo_album')->insert(['photo_id' => $photo->id, 'album_id' => $album_a->id]);
		DB::table('photo_album')->insert(['photo_id' => $photo->id, 'album_id' => $album_b->id]);
		$this->recompute($album_a);
		$this->recompute($album_b);

		$response_a = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album_a->id}/Photos/buckets");
		$this->assertOk($response_a);
		$response_a->assertJson(['bucket_ids' => ['2022']]);

		$response_b = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album_b->id}/Photos/buckets");
		$this->assertOk($response_b);
		$response_b->assertJson(['bucket_ids' => ['2022-06']]);
	}

	// ── Managed cache ──────────────────────────────────────────────

	public function testCacheHitSkipsTheAggregationQuery(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$this->setInstanceDefaults('created_at', 'year');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		$this->recompute($album);

		$this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets")->assertOk();

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets")->assertOk();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$aggregation_queries = array_filter($log, fn (array $q) => preg_match('/group by/i', (string) $q['query']) === 1);
		self::assertCount(0, $aggregation_queries, 'A cache hit must not re-run the GROUP BY aggregation query.');
	}

	public function testCacheInvalidatedOnPhotoUploadedIntoAlbum(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$this->setInstanceDefaults('created_at', 'year');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		$this->recompute($album);

		$before = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets")->assertOk()->json('counts');
		self::assertSame([1], $before);

		$new_photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		\App\Events\PhotoSaved::dispatch([$new_photo->id]);
		$this->recompute($album);

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets")->assertOk()->json('counts');
		self::assertSame([2], $after);
	}

	/**
	 * Two different identities requesting the same album's tiers never
	 * share a cache entry.
	 */
	public function testNoCrossIdentityCacheLeakage(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->album1->id}/Photos/buckets")->assertOk();

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->actingAs($this->admin)->getJsonV3("Albums/{$this->album1->id}/Photos/buckets")->assertOk();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$photo_album_queries = array_filter($log, fn (array $q) => str_contains(strtolower((string) $q['query']), 'photo_album'));
		self::assertGreaterThan(0, count($photo_album_queries), 'A different caller must not be served from another identity\'s cached entry.');
	}

	public function testCacheInvalidatedOnAlbumPhotoSortingChange(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$this->setInstanceDefaults('created_at', 'year');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['created_at' => new Carbon('2023-01-01')]);
		$this->recompute($album);

		$before = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets")->assertOk()->json('bucket_ids');
		self::assertSame(['2023'], $before);

		$album->photo_timeline = \App\Enum\TimelinePhotoGranularity::MONTH;
		$album->save();
		\App\Events\AlbumPhotoSortingChanged::dispatch([$album->id]);
		$this->recompute($album);

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets")->assertOk()->json('bucket_ids');
		self::assertSame(['2023-01'], $after);
	}
}
