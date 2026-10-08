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

use App\Facades\Helpers;
use App\Jobs\RecomputeAlbumPhotoBucketsJob;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Photo;
use App\Models\Statistics;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers `GET /Albums/{album_id}/Photos/details`.
 */
class PhotoDetailsV3Test extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
	}

	// ── Flag gate ─────────────────────────────────────────────────

	public function testFlagOffReturns403(): void
	{
		config(['features.struct-of-array' => false]);
		$response = $this->actingAs($this->admin)->getJsonV3("Albums/{$this->album1->id}/Photos/details", ['bucket_id' => 'unknown']);
		$this->assertForbidden($response);
	}

	// ── Scoping validation ─────────────────────────────────────────

	public function testNeitherParamReturns422(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->album1->id}/Photos/details");
		$this->assertUnprocessable($response);
	}

	public function testBothParamsReturns422(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->album1->id}/Photos/details", [
			'bucket_id' => 'unknown',
			'photo_ids' => [$this->photo1->id],
		]);
		$this->assertUnprocessable($response);
	}

	public function test301PhotoIdsReturns422And300ReturnsOk(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$ids = [];
		for ($i = 0; $i < 300; $i++) {
			$ids[] = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create()->id;
		}

		$response_301 = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/details", [
			'photo_ids' => [...$ids, 'AAAAAAAAAAAAAAAAAAAAAAAA'],
		]);
		$this->assertUnprocessable($response_301);

		$response_300 = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/details", [
			'photo_ids' => $ids,
		]);
		$this->assertOk($response_300);
		$this->assertCount(300, $response_300->json('ids'));
	}

	// ── bucket_id scoping ──────────────────────────────────────────

	public function testBucketIdModeReturnsExactlyThatBucketsPhotosUncapped(): void
	{
		DB::table('configs')->where('key', '=', 'sorting_photos_col')->update(['value' => 'is_highlighted']);
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$highlighted = [];
		for ($i = 0; $i < 40; $i++) {
			$highlighted[] = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_highlighted' => true])->id;
		}
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_highlighted' => false]);
		(new RecomputeAlbumPhotoBucketsJob($album->id))->handle();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/details", ['bucket_id' => '1']);
		$this->assertOk($response);
		$returned_ids = $response->json('ids');
		$this->assertCount(40, $returned_ids, 'bucket_id mode must return every matching photo, uncapped, no truncation.');
		sort($highlighted);
		sort($returned_ids);
		$this->assertSame($highlighted, $returned_ids);
	}

	public function testUnknownBucketSentinelMapsToNullBucketId(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$undated = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['taken_at' => null]);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['taken_at' => new \Illuminate\Support\Carbon('2022-01-01')]);
		DB::table('configs')->where('key', '=', 'sorting_photos_col')->update(['value' => 'taken_at']);
		(new RecomputeAlbumPhotoBucketsJob($album->id))->handle();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/details", ['bucket_id' => 'unknown']);
		$this->assertOk($response);
		$response->assertJson(['ids' => [$undated->id]]);
	}

	// ── photo_ids[] scoping ────────────────────────────────────────

	public function testPhotoIdsModeSilentlyCuratesInvisibleAndForeignIds(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$visible = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		$other_album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$not_in_album = Photo::factory()->owned_by($this->userMayUpload1)->in($other_album)->create();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/details", [
			'photo_ids' => [$visible->id, $not_in_album->id, 'AAAAAAAAAAAAAAAAAAAAAAAA'],
		]);
		$this->assertOk($response);
		$response->assertJson(['ids' => [$visible->id]]);
	}

	// ── Config-gated field omission ─────────────────────────────────

	public function testExifGateOffOmitsExifFields(): void
	{
		DB::table('configs')->where('key', '=', 'display_exif_data')->update(['value' => '0']);
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/details", ['photo_ids' => [$photo->id]]);
		$this->assertOk($response);
		$this->assertArrayNotHasKey('makes', $response->json());
	}

	public function testGuestWithGpsPublicDisabledOmitsGpsFieldsButUserSeesThem(): void
	{
		DB::table('configs')->where('key', '=', 'gps_coordinate_display')->update(['value' => '1']);
		DB::table('configs')->where('key', '=', 'gps_coordinate_display_public')->update(['value' => '0']);
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		AccessPermission::factory()->public()->visible()->for_album($album)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->with_GPS_coordinates()->in($album)->create();

		$guest_response = $this->getJsonV3("Albums/{$album->id}/Photos/details", ['photo_ids' => [$photo->id]]);
		$this->assertOk($guest_response);
		$this->assertArrayNotHasKey('latitudes', $guest_response->json());

		$user_response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/details", ['photo_ids' => [$photo->id]]);
		$this->assertOk($user_response);
		$this->assertArrayHasKey('latitudes', $user_response->json());
	}

	// ── Full PhotoResource reconstruction parity ──────────────────

	public function testRatiosAndDetailsCombinedReconstructKeyPhotoResourceFields(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$tag = Tag::factory()->create(['name' => 'sunset']);
		$photo = Photo::factory()
			->owned_by($this->userMayUpload1)
			->with_GPS_coordinates()
			->with_palette()
			->in($album)
			->create(['description' => 'a lovely sunset', 'rating_avg' => '4.0000']);
		$photo->tags()->attach($tag);

		$ratios = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos")->assertOk()->json();
		$details = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/details", ['photo_ids' => [$photo->id]])->assertOk()->json();

		$this->assertSame($photo->id, $ratios['ids'][0]);
		$this->assertSame($photo->id, $details['ids'][0]);
		$this->assertSame($photo->owner_id, $ratios['owner_ids'][0]);
		$this->assertSame('a lovely sunset', $details['descriptions'][0]);
		$this->assertSame(['sunset'], $details['tags'][0]);
		$this->assertEqualsWithDelta(4.0, $details['rating_avgs'][0], 0.0001);
		$this->assertSame($photo->checksum, $details['checksums'][0]);
		$this->assertSame($photo->original_checksum, $details['original_checksums'][0]);
		$this->assertNotNull($details['palette'][0]);
		$this->assertNotNull($details['size_variants'][0]);
		$this->assertSame($photo->license->value, $details['licenses'][0]);
	}

	// ── Video info and date flags (parity with PreformattedPhotoData / PreComputedPhotoData) ──

	public function testDurationsAndFpsMatchPreformattedPhotoData(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['duration' => 65, 'fps' => '30']);

		$details = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/details", ['photo_ids' => [$photo->id]])->assertOk()->json();

		$this->assertSame(Helpers::secondsToHMS(65), $details['durations'][0]);
		$this->assertSame('30 fps', $details['fps'][0]);
	}

	public function testDurationsAndFpsEmptyWhenAbsent(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['duration' => null, 'fps' => null]);

		$details = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/details", ['photo_ids' => [$photo->id]])->assertOk()->json();

		$this->assertSame('', $details['fps'][0]);
	}

	public function testExifGateOffOmitsDurationsAndFps(): void
	{
		DB::table('configs')->where('key', '=', 'display_exif_data')->update(['value' => '0']);
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();

		$details = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/details", ['photo_ids' => [$photo->id]])->assertOk()->json();

		$this->assertArrayNotHasKey('durations', $details);
		$this->assertArrayNotHasKey('fps', $details);
	}

	public function testDateFlagsMatchPreComputedPhotoData(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$modified = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create([
			'taken_at' => Carbon::parse('2024-05-01 10:00:00'),
			'initial_taken_at' => Carbon::parse('2024-04-01 10:00:00'),
		]);
		$untouched = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create([
			'taken_at' => Carbon::parse('2024-05-01 10:00:00'),
			'initial_taken_at' => Carbon::parse('2024-05-01 10:00:00'),
		]);
		$no_date = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['taken_at' => null, 'initial_taken_at' => null]);

		$details = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/details", [
			'photo_ids' => [$modified->id, $untouched->id, $no_date->id],
		])->assertOk()->json();

		$by_id = [];
		foreach ($details['ids'] as $i => $id) {
			$by_id[$id] = [$details['is_camera_dates'][$i], $details['is_taken_at_modifieds'][$i]];
		}
		$this->assertSame([true, true], $by_id[$modified->id]);
		$this->assertSame([true, false], $by_id[$untouched->id]);
		$this->assertSame([false, false], $by_id[$no_date->id]);
	}

	// ── Description rendering ─────────────────────────────────────

	public function testPreformattedDescriptionsAreMarkdownRenderedWithRawHtmlStripped(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$raw = '**bold** <script>alert(1)</script><img src=x onerror=alert(2)>';
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['description' => $raw]);
		$empty = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['description' => null]);

		$details = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/details", ['photo_ids' => [$photo->id, $empty->id]])->assertOk()->json();

		$idx = array_search($photo->id, $details['ids'], true);
		$empty_idx = array_search($empty->id, $details['ids'], true);
		$this->assertSame($raw, $details['descriptions'][$idx]);
		$html = $details['preformatted_descriptions'][$idx];
		$this->assertStringContainsString('<strong>bold</strong>', $html);
		$this->assertStringNotContainsString('<script', $html);
		$this->assertStringNotContainsString('<img', $html);
		$this->assertSame('', $details['preformatted_descriptions'][$empty_idx]);
	}

	// ── metrics_access=owner per-row visibility ───────────────────

	public function testMetricsAccessOwnerIsPerRowNotPerRequest(): void
	{
		DB::table('configs')->where('key', '=', 'metrics_enabled')->update(['value' => '1']);
		DB::table('configs')->where('key', '=', 'metrics_access')->update(['value' => 'owner']);
		DB::table('configs')->where('key', '=', 'sorting_photos_col')->update(['value' => 'is_highlighted']);

		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();

		$own_photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_highlighted' => true]);
		Statistics::query()->where('photo_id', '=', $own_photo->id)->update(['visit_count' => 5]);
		$other_photo = Photo::factory()->owned_by($this->userMayUpload2)->in($album)->create(['is_highlighted' => true]);
		Statistics::query()->where('photo_id', '=', $other_photo->id)->update(['visit_count' => 9]);
		(new RecomputeAlbumPhotoBucketsJob($album->id))->handle();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/details", ['bucket_id' => '1']);
		$this->assertOk($response);
		$json = $response->json();

		$own_index = array_search($own_photo->id, $json['ids'], true);
		$other_index = array_search($other_photo->id, $json['ids'], true);
		$this->assertNotNull($json['statistics'][$own_index]);
		$this->assertNull($json['statistics'][$other_index]);
	}

	// ── Access / resolution edge cases ────────────────────────────

	public function testTagAlbumIdSucceeds(): void
	{
		// tagAlbum1 matches photo1 (tagged `test`) only.
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->tagAlbum1->id}/Photos/details", ['photo_ids' => [$this->photo1->id]]);
		$this->assertOk($response);
		$response->assertJson(['ids' => [$this->photo1->id]]);
	}

	/**
	 * `bucket_id` mode for a non-`Album` source has no stored `bucket_id`
	 * column to filter by directly - it resolves matching ids through a
	 * lean live-computation pass first ({@see \App\Actions\Photo\StructOfArrays\QueryPhotoDetails::resolveLiveBucketPhotoIds()}).
	 * This exercises that path directly, distinct from `photo_ids` mode.
	 */
	public function testTagAlbumBucketIdModeSucceeds(): void
	{
		DB::table('configs')->where('key', '=', 'sorting_photos_col')->update(['value' => 'created_at']);
		DB::table('configs')->where('key', '=', 'timeline_photos_granularity')->update(['value' => 'year']);
		$bucket_id = $this->photo1->created_at->format('Y');

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->tagAlbum1->id}/Photos/details", ['bucket_id' => $bucket_id]);
		$this->assertOk($response);
		$response->assertJson(['ids' => [$this->photo1->id]]);
	}

	public function testSmartAlbumIdSucceeds(): void
	{
		$this->photo1->is_highlighted = true;
		$this->photo1->save();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/highlighted/Photos/details', ['photo_ids' => [$this->photo1->id]]);
		$this->assertOk($response);
		$response->assertJson(['ids' => [$this->photo1->id]]);
	}

	public function testNoAccessReturns403(): void
	{
		$response = $this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$this->album1->id}/Photos/details", ['bucket_id' => 'unknown']);
		$this->assertForbidden($response);
	}

	public function testZeroMatchingPhotosReturnsEmptyArrays(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/details", ['bucket_id' => 'unknown']);
		$this->assertOk($response);
		$response->assertJson(['ids' => []]);
	}
}
