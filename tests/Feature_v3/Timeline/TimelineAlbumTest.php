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

namespace Tests\Feature_v3\Timeline;

use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers `App\SmartAlbums\TimelineAlbum` end-to-end, reached as
 * `album_id='timeline'` through the existing v3 photo-tier routes
 * (`GET /Albums/timeline/Photos/buckets`, `/Photos`, `/Photos/details`).
 *
 * Builds its own isolated fixture per test, mirroring
 * `Tests\Feature_v3\Photo\PhotoBucketsV3Test`.
 */
class TimelineAlbumTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
	}

	// ── Access gating (F-066-03, S-066-02, S-066-03) ────────────────

	public function testGuestDeniedByDefault(): void
	{
		$response = $this->getJsonV3('Albums/timeline/Photos/buckets');
		$this->assertUnauthorized($response);
	}

	public function testGuestAllowedWhenTimelinePhotosPublic(): void
	{
		Configs::set('timeline_photos_public', '1');

		$response = $this->getJsonV3('Albums/timeline/Photos/buckets');
		$this->assertOk($response);

		Configs::set('timeline_photos_public', '0');
	}

	public function testAuthenticatedUserAlwaysAllowedRegardlessOfTimelinePhotosPublic(): void
	{
		Configs::set('timeline_photos_public', '0');

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/timeline/Photos/buckets');
		$this->assertOk($response);
	}

	public function testTimelinePageDisabledReturnsForbiddenRegardlessOfAuth(): void
	{
		Configs::set('timeline_page_enabled', '0');

		$guest_response = $this->getJsonV3('Albums/timeline/Photos/buckets');
		$this->assertUnauthorized($guest_response);

		$auth_response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/timeline/Photos/buckets');
		$this->assertForbidden($auth_response);

		Configs::set('timeline_page_enabled', '1');
	}

	// ── End-to-end tier verification (F-066-01, F-066-02, S-066-01) ─

	public function testBucketsRatiosAndDetailsAllServeTimelineData(): void
	{
		DB::table('configs')->where('key', '=', 'timeline_photos_order')->update(['value' => 'created_at']);
		DB::table('configs')->where('key', '=', 'timeline_photos_granularity')->update(['value' => 'year']);

		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create(['created_at' => new Carbon('2024-05-01')]);

		$buckets = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/timeline/Photos/buckets');
		$this->assertOk($buckets);
		$buckets_json = $buckets->json();
		self::assertContains('2024', $buckets_json['bucket_ids']);
		self::assertTrue($buckets_json['bucketable']);

		$ratios = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/timeline/Photos');
		$this->assertOk($ratios);
		self::assertContains($photo->id, $ratios->json('ids'));

		$details = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/timeline/Photos/details', ['bucket_id' => '2024']);
		$this->assertOk($details);
		self::assertContains($photo->id, $details->json('ids'));
	}

	public function testUnknownAlbumIdStillReturns404(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/AAAAAAAAAAAAAAAAAAAAAAAA/Photos/buckets');
		$this->assertNotFound($response);
	}

	// ── SQL-pushdown buckets tier (F-066-05, NFR-066-01, S-066-01) ──

	public function testBucketsGroupsByYearWithCountsOrderedDescendingAndUnknownLast(): void
	{
		DB::table('configs')->where('key', '=', 'timeline_photos_order')->update(['value' => 'created_at']);
		DB::table('configs')->where('key', '=', 'timeline_photos_granularity')->update(['value' => 'year']);

		$owner = \App\Models\User::factory()->may_upload()->create();
		$album = \App\Models\Album::factory()->as_root()->owned_by($owner)->create();
		Photo::factory()->owned_by($owner)->in($album)->create(['created_at' => new Carbon('2022-01-01')]);
		Photo::factory()->owned_by($owner)->in($album)->create(['created_at' => new Carbon('2024-01-01')]);
		Photo::factory()->owned_by($owner)->in($album)->create(['created_at' => new Carbon('2024-06-01')]);

		$response = $this->actingAs($owner)->getJsonV3('Albums/timeline/Photos/buckets');
		$this->assertOk($response);
		$json = $response->json();
		// Base fixture's own publicly-shared photos (album4/subAlbum4,
		// visible to every authenticated viewer) may also surface a bucket
		// here - assert on the two buckets this test created, not on exact
		// array equality.
		$by_bucket = array_combine($json['bucket_ids'], $json['counts']);
		self::assertSame(2, $by_bucket['2024']);
		self::assertSame(1, $by_bucket['2022']);
		// DESC order: '2024' must sort before '2022'.
		self::assertLessThan(array_search('2022', $json['bucket_ids'], true), array_search('2024', $json['bucket_ids'], true));
	}

	public function testBucketsQueryCountIsBoundedByBucketCountNotPhotoCount(): void
	{
		DB::table('configs')->where('key', '=', 'timeline_photos_order')->update(['value' => 'created_at']);
		DB::table('configs')->where('key', '=', 'timeline_photos_granularity')->update(['value' => 'year']);

		$owner = \App\Models\User::factory()->may_upload()->create();
		$album = \App\Models\Album::factory()->as_root()->owned_by($owner)->create();
		for ($i = 0; $i < 25; $i++) {
			Photo::factory()->owned_by($owner)->in($album)->create(['created_at' => new Carbon('2024-01-01')]);
		}

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->actingAs($owner)->getJsonV3('Albums/timeline/Photos/buckets')->assertOk();
		$query_count = count(DB::getQueryLog());
		DB::flushQueryLog();
		DB::disableQueryLog();

		// A handful of fixed queries (the GROUP BY itself, the unknown-count,
		// access-permission lookups, plus one-off connection/schema warm-up
		// overhead) regardless of the 25 candidate photos - well under one
		// query per photo, which a full-library PHP row scan
		// (queryLiveBuckets()) would need instead.
		self::assertLessThan(25, $query_count);
	}

	// ── SQL-pushdown details tier (F-066-07, NFR-066-01, S-066-08) ──

	public function testDetailsBucketScopedResolvesOnlyThatBucketsPhotosViaPushdown(): void
	{
		DB::table('configs')->where('key', '=', 'timeline_photos_order')->update(['value' => 'created_at']);
		DB::table('configs')->where('key', '=', 'timeline_photos_granularity')->update(['value' => 'year']);

		$owner = \App\Models\User::factory()->may_upload()->create();
		$album = \App\Models\Album::factory()->as_root()->owned_by($owner)->create();
		$photo_2024 = Photo::factory()->owned_by($owner)->in($album)->create(['created_at' => new Carbon('2024-05-01')]);
		$photo_2022 = Photo::factory()->owned_by($owner)->in($album)->create(['created_at' => new Carbon('2022-05-01')]);

		$response = $this->actingAs($owner)->getJsonV3('Albums/timeline/Photos/details', ['bucket_id' => '2024']);
		$this->assertOk($response);
		$ids = $response->json('ids');
		self::assertContains($photo_2024->id, $ids);
		self::assertNotContains($photo_2022->id, $ids);
	}

	public function testDetailsUnknownBucketResolvesOnlyNullSortColumnPhotos(): void
	{
		DB::table('configs')->where('key', '=', 'timeline_photos_order')->update(['value' => 'taken_at']);
		DB::table('configs')->where('key', '=', 'timeline_photos_granularity')->update(['value' => 'year']);

		$owner = \App\Models\User::factory()->may_upload()->create();
		$album = \App\Models\Album::factory()->as_root()->owned_by($owner)->create();
		$dated_photo = Photo::factory()->owned_by($owner)->in($album)->create(['taken_at' => new Carbon('2024-05-01')]);
		$undated_photo = Photo::factory()->owned_by($owner)->in($album)->create(['taken_at' => null]);

		$response = $this->actingAs($owner)->getJsonV3('Albums/timeline/Photos/details', ['bucket_id' => 'unknown']);
		$this->assertOk($response);
		$ids = $response->json('ids');
		self::assertContains($undated_photo->id, $ids);
		self::assertNotContains($dated_photo->id, $ids);
	}

	// ── Windowed ratios (F-066-06, F-066-07, S-066-05, S-066-07) ────

	public function testRatiosBucketIdsWindowReturnsOnlyThatBucketsPhotosViaPushdown(): void
	{
		DB::table('configs')->where('key', '=', 'timeline_photos_order')->update(['value' => 'created_at']);
		DB::table('configs')->where('key', '=', 'timeline_photos_granularity')->update(['value' => 'year']);

		$owner = \App\Models\User::factory()->may_upload()->create();
		$album = \App\Models\Album::factory()->as_root()->owned_by($owner)->create();
		$photo_2024 = Photo::factory()->owned_by($owner)->in($album)->create(['created_at' => new Carbon('2024-05-01')]);
		$photo_2022 = Photo::factory()->owned_by($owner)->in($album)->create(['created_at' => new Carbon('2022-05-01')]);

		$response = $this->actingAs($owner)->getJsonV3('Albums/timeline/Photos', ['bucket_ids' => ['2024']]);
		$this->assertOk($response);
		$ids = $response->json('ids');
		self::assertContains($photo_2024->id, $ids);
		self::assertNotContains($photo_2022->id, $ids);
	}

	public function testRatiosPhotoIdsResolvesExactlyThatPhotoWithCorrectBucketId(): void
	{
		DB::table('configs')->where('key', '=', 'timeline_photos_order')->update(['value' => 'created_at']);
		DB::table('configs')->where('key', '=', 'timeline_photos_granularity')->update(['value' => 'year']);

		$owner = \App\Models\User::factory()->may_upload()->create();
		$album = \App\Models\Album::factory()->as_root()->owned_by($owner)->create();
		$target = Photo::factory()->owned_by($owner)->in($album)->create(['created_at' => new Carbon('2024-05-01')]);
		Photo::factory()->owned_by($owner)->in($album)->create(['created_at' => new Carbon('2022-05-01')]);

		$response = $this->actingAs($owner)->getJsonV3('Albums/timeline/Photos', ['photo_ids' => [$target->id]]);
		$this->assertOk($response);
		$response->assertJson(['ids' => [$target->id], 'bucket_ids' => ['2024']]);
	}

	public function testRatiosBucketIdsAndPhotoIdsBothProvidedReturns422(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/timeline/Photos', [
			'bucket_ids' => ['2024'],
			'photo_ids' => [$this->photo1->id],
		]);
		self::assertSame(422, $response->getStatusCode());
	}

	// ── Sort-column fallback (F-066-04, S-066-12) ───────────────────

	public function testUnsupportedSortColumnFallsBackToTakenAt(): void
	{
		DB::table('configs')->where('key', '=', 'timeline_photos_order')->update(['value' => 'title']);
		DB::table('configs')->where('key', '=', 'timeline_photos_granularity')->update(['value' => 'year']);

		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create(['taken_at' => new Carbon('2021-06-15')]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/timeline/Photos/buckets');
		$this->assertOk($response);
		self::assertContains('2021', $response->json('bucket_ids'));

		$ratios = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/timeline/Photos');
		$this->assertOk($ratios);
		self::assertContains($photo->id, $ratios->json('ids'));
	}

	// ── NSFW config key isolation (F-066-02, S-066-13) ──────────────

	public function testHideNsfwInTimelineHidesSensitiveAlbumPhotosIndependentlyOfSmartAlbumConfig(): void
	{
		DB::table('configs')->where('key', '=', 'timeline_photos_order')->update(['value' => 'created_at']);
		Configs::set('hide_nsfw_in_timeline', '1');
		// Deliberately the OPPOSITE, to prove Timeline never reads this key.
		Configs::set('hide_nsfw_in_smart_albums', '0');

		$this->album1->is_nsfw = true;
		$this->album1->save();

		$sensitive_photo = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/timeline/Photos');
		$this->assertOk($response);
		self::assertNotContains($sensitive_photo->id, $response->json('ids'));

		Configs::set('hide_nsfw_in_timeline', '0');
		$response_shown = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/timeline/Photos');
		$this->assertOk($response_shown);
		self::assertContains($sensitive_photo->id, $response_shown->json('ids'));

		Configs::set('hide_nsfw_in_timeline', '1');
		$this->album1->is_nsfw = false;
		$this->album1->save();
	}
}
