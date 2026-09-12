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

use App\Enum\SizeVariantType;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers `GET /Albums/{album_id}/Photos`.
 *
 * Builds its own isolated fixture per test, mirroring `PhotoBucketsV3Test`.
 */
class PhotoRatiosV3Test extends BaseApiWithDataTest
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
		$response = $this->actingAs($this->admin)->getJsonV3("Albums/{$this->album1->id}/Photos");
		$this->assertForbidden($response);
	}

	// ── Baseline unconditional fields ────────────────────────────

	public function testUnconditionalFieldsForOnePhoto(): void
	{
		DB::table('configs')->where('key', '=', 'display_thumb_photo_overlay')->update(['value' => 'never']);
		DB::table('configs')->where('key', '=', 'rating_enabled')->update(['value' => '0']);
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_highlighted' => true]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos");
		$this->assertOk($response);
		$json = $response->json();

		$this->assertSame([$photo->id], $json['ids']);
		$this->assertSame([$photo->title], $json['titles']);
		$this->assertSame(['image/jpeg'], $json['types']);
		$this->assertSame([$photo->owner_id], $json['owner_ids']);
		$this->assertSame([true], $json['is_highlighteds']);
		$this->assertSame([true], $json['is_validateds']);
		$this->assertSame([false], $json['is_videos']);
		$this->assertSame([false], $json['is_raws']);
		$this->assertSame([false], $json['is_live_photos']);
		$this->assertSame([1.5], $json['ratios']);
		$this->assertArrayNotHasKey('rating_avgs', $json);
		$this->assertArrayNotHasKey('thumb_infos', $json);
		$this->assertArrayNotHasKey('tags', $json);
	}

	// ── Ratio resolution ───────────────────────────────────────────

	public function testRatioFallbackToOneWhenOnlyThumbVariantExists(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		// Factory chaining quirk: without_size_variants()'s flag is not
		// reliably observed by the afterCreating() hook once state()/
		// hasAttached() clone the factory afterwards - explicitly clear
		// whatever the factory did create instead of relying on the flag.
		SizeVariant::query()->where('photo_id', '=', $photo->id)->delete();
		SizeVariant::factory()->create(['photo_id' => $photo->id, 'type' => SizeVariantType::THUMB, 'ratio' => 1.5]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos");
		$this->assertOk($response);
		$response->assertJson(['ratios' => [1.0]]);
	}

	/**
	 * A video with only an ORIGINAL size variant (no MEDIUM/SMALL) and a
	 * real, non-1 ratio must report that real ratio, not the forced `1`
	 * `Photo::getAspectRatioAttribute()`'s video branch would produce.
	 */
	public function testVideoWithOnlyOriginalUsesRealRatioNotForcedOne(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['type' => 'video/mp4']);
		SizeVariant::query()->where('photo_id', '=', $photo->id)->delete();
		SizeVariant::factory()->create(['photo_id' => $photo->id, 'type' => SizeVariantType::ORIGINAL, 'ratio' => 1.777]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos");
		$this->assertOk($response);
		$response->assertJson(['ratios' => [1.777], 'is_videos' => [true]]);
	}

	public function testRatioResolutionUsesExactlyThreeSizeVariantJoinsInOneQuery(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos")->assertOk();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		// Filter specifically for the ratios listing query itself (identified
		// by its distinctive size_variants joins) - the album/auth
		// resolution layer separately issues its own unrelated `photos`
		// queries (e.g. AlbumPolicy::CAN_ACCESS gate resolution), which are
		// out of scope for this assertion.
		$listing_queries = array_values(array_filter($log, fn (array $q) => str_contains(strtolower((string) $q['query']), 'size_variants')));
		$this->assertCount(1, $listing_queries, 'Expected exactly one photos-listing query regardless of album photo count.');
		$join_count = substr_count(strtolower((string) $listing_queries[0]['query']), 'left join "size_variants"') + substr_count(strtolower((string) $listing_queries[0]['query']), 'left join `size_variants`');
		$this->assertSame(3, $join_count, 'Expected exactly 3 fixed size_variants joins, never N+1.');
	}

	// ── Config-gated field omission matrix ──────────────────────────

	public function testRatingGateOffOmitsRatingFields(): void
	{
		DB::table('configs')->where('key', '=', 'rating_enabled')->update(['value' => '0']);
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos");
		$this->assertOk($response);
		$this->assertArrayNotHasKey('rating_avgs', $response->json());
		$this->assertArrayNotHasKey('rating_users', $response->json());
	}

	public function testRatingGateOnIncludesRatingFields(): void
	{
		DB::table('configs')->where('key', '=', 'rating_enabled')->update(['value' => '1']);
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['rating_avg' => '4.0000']);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos");
		$this->assertOk($response);
		$response->assertJson(['rating_avgs' => [4.0]]);
		$this->assertArrayHasKey('rating_users', $response->json());
	}

	public function testDisplayThumbOverlayNeverOmitsThumbInfosAndTags(): void
	{
		DB::table('configs')->where('key', '=', 'display_thumb_photo_overlay')->update(['value' => 'never']);
		DB::table('configs')->where('key', '=', 'photo_thumb_info')->update(['value' => 'description']);
		DB::table('configs')->where('key', '=', 'photo_thumb_tags_enabled')->update(['value' => '1']);
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos");
		$this->assertOk($response);
		$this->assertArrayNotHasKey('thumb_infos', $response->json());
		$this->assertArrayNotHasKey('tags', $response->json());
	}

	public function testDescriptionModeIncludesThumbInfosOnlyNotTags(): void
	{
		DB::table('configs')->where('key', '=', 'display_thumb_photo_overlay')->update(['value' => 'always']);
		DB::table('configs')->where('key', '=', 'photo_thumb_info')->update(['value' => 'description']);
		DB::table('configs')->where('key', '=', 'photo_thumb_tags_enabled')->update(['value' => '1']);
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['description' => 'hello world']);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos");
		$this->assertOk($response);
		$this->assertArrayHasKey('thumb_infos', $response->json());
		$this->assertArrayNotHasKey('tags', $response->json());
	}

	public function testTitleModeWithTagsEnabledIncludesTagsOnlyNotThumbInfos(): void
	{
		DB::table('configs')->where('key', '=', 'display_thumb_photo_overlay')->update(['value' => 'always']);
		DB::table('configs')->where('key', '=', 'photo_thumb_info')->update(['value' => 'title']);
		DB::table('configs')->where('key', '=', 'photo_thumb_tags_enabled')->update(['value' => '1']);
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		$tag = Tag::factory()->create(['name' => 'landscape']);
		$photo->tags()->attach($tag);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos");
		$this->assertOk($response);
		$this->assertArrayNotHasKey('thumb_infos', $response->json());
		$response->assertJson(['tags' => [['landscape']]]);
	}

	// ── Bucket correlation ─────────────────────────────────────────

	public function testRatiosGroupedByBucketIdReproducesBucketsCounts(): void
	{
		DB::table('configs')->where('key', '=', 'sorting_photos_col')->update(['value' => 'is_highlighted']);
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_highlighted' => true]);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_highlighted' => false]);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_highlighted' => false]);
		(new \App\Jobs\RecomputeAlbumPhotoBucketsJob($album->id))->handle();

		$buckets = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets")->assertOk()->json();
		$ratios = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos")->assertOk()->json();

		$grouped = [];
		foreach ($ratios['bucket_ids'] as $bucket_id) {
			$grouped[$bucket_id] = ($grouped[$bucket_id] ?? 0) + 1;
		}
		ksort($grouped);
		$expected = array_combine($buckets['bucket_ids'], $buckets['counts']);
		ksort($expected);
		$this->assertSame($expected, $grouped);
	}

	// ── Upload-validation curation ────────────────────────────────

	public function testNonAdminExcludesOtherUsersUnvalidatedUpload(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$visible = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_validated' => true]);
		Photo::factory()->owned_by($this->userMayUpload2)->in($album)->create(['is_validated' => false]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos");
		$this->assertOk($response);
		$response->assertJson(['ids' => [$visible->id]]);
	}

	// ── Raw dates, no Carbon ───────────────────────────────────────

	public function testDatesAreRawIso8601RegardlessOfDateFormatPhotoThumb(): void
	{
		DB::table('configs')->where('key', '=', 'date_format_photo_thumb')->update(['value' => 'd/m/Y']);
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['taken_at' => new \Illuminate\Support\Carbon('2022-06-15T10:00:00Z')]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos");
		$this->assertOk($response);
		$json = $response->json();
		// Raw DB datetime string, never reformatted via the configured
		// 'd/m/Y' display format - accepts either the 'T'-separated ISO
		// 8601 form or a driver's native space-separated timestamp string,
		// since this tier deliberately never instantiates a Carbon object
		// to normalize it.
		$this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}/', $json['taken_ats'][0]);
	}

	public function testCodeReviewNoCarbonCallInQueryOrResourceFiles(): void
	{
		$query_source = file_get_contents(base_path('app/Actions/Photo/StructOfArrays/QueryPhotoRatios.php'));
		$resource_source = file_get_contents(base_path('app/Http/Resources/V3/PhotoRatioResource.php'));
		$this->assertStringNotContainsString('Carbon::', (string) $query_source);
		$this->assertStringNotContainsString('->format(', (string) $query_source);
		$this->assertStringNotContainsString('Carbon::', (string) $resource_source);
	}

	// ── Access / resolution edge cases ────────────────────────────

	public function testTagAlbumIdSucceedsWithLiveComputedBucketIds(): void
	{
		// tagAlbum1 matches photo1 (tagged `test`) only - photo1b (untagged,
		// same album) and every other fixture photo must not appear.
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->tagAlbum1->id}/Photos");
		$this->assertOk($response);
		$json = $response->json();
		$this->assertSame([$this->photo1->id], $json['ids']);
		$this->assertNotSame('unknown', $json['bucket_ids'][0]);
	}

	public function testSmartAlbumIdSucceeds(): void
	{
		$this->photo1->is_highlighted = true;
		$this->photo1->save();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/highlighted/Photos');
		$this->assertOk($response);
		$response->assertJson(['ids' => [$this->photo1->id]]);
	}

	/**
	 * `BaseSmartAlbum::photos()` left-joins `photo_album` without any
	 * `album_id` restriction - a photo belonging to more than one regular
	 * album must still appear exactly once here, not once per membership
	 * (see `ResolvesPhotoSource::resolvePhotoQuery()`'s `whereIn`
	 * id-subquery fix for this exact fan-out).
	 */
	public function testSmartAlbumDeduplicatesPhotoBelongingToMultipleAlbums(): void
	{
		$this->photo1->is_highlighted = true;
		$this->photo1->save();
		// photo1 already belongs to album1 (base fixture) - attach it to a
		// second, same-owner album too.
		$this->photo1->albums()->attach($this->subAlbum1->id);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/highlighted/Photos');
		$this->assertOk($response);
		$json = $response->json();
		$this->assertSame([$this->photo1->id], $json['ids']);
	}

	public function testPersonAlbumIdSucceeds(): void
	{
		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		$person = Person::factory()->create(['name' => 'Alice', 'is_searchable' => true]);
		Face::factory()->for_photo($this->photo1)->for_person($person)->create();

		// PersonAlbum creation is a v2 (JSON) endpoint - reused here purely
		// as fixture setup, mirroring Tests\AssistedVision\PersonAlbumTest;
		// there is no PersonAlbum::factory() equivalent that also wires up
		// matching Face rows.
		$create_response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'person_album_v3_test',
			'persons' => [$person->id],
			'is_and' => false,
		]);
		$this->assertOk($create_response);
		$person_album_id = $create_response->getOriginalContent();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$person_album_id}/Photos");
		$this->assertOk($response);
		$response->assertJson(['ids' => [$this->photo1->id]]);
	}

	public function testNoAccessReturns403(): void
	{
		$response = $this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$this->album1->id}/Photos");
		$this->assertForbidden($response);
	}

	public function testZeroPhotosAlbumReturnsEmptyArrays(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos");
		$this->assertOk($response);
		$response->assertJson(['ids' => []]);
	}
}
