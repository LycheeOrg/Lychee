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
use App\Enum\TimelinePhotoGranularity;
use App\Jobs\RecomputeAlbumPhotoBucketsJob;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\TagAlbum;
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

	public function testTagAlbumIdSucceedsWithLiveComputedBuckets(): void
	{
		$this->setInstanceDefaults('created_at', 'year');
		// tagAlbum1 matches photo1 (tagged `test`) only - photo1b (untagged,
		// same album) and every other fixture photo must not appear.
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->tagAlbum1->id}/Photos/buckets");
		$this->assertOk($response);
		$response->assertJson([
			'bucket_ids' => [$this->photo1->created_at->format('Y')],
			'counts' => [1],
			'bucketable' => true,
		]);
	}

	public function testSmartAlbumIdSucceedsWithLiveComputedBuckets(): void
	{
		$this->setInstanceDefaults('created_at', 'year');
		$this->photo1->is_highlighted = true;
		$this->photo1->save();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/highlighted/Photos/buckets');
		$this->assertOk($response);
		$response->assertJson([
			'bucket_ids' => [$this->photo1->created_at->format('Y')],
			'counts' => [1],
			'bucketable' => true,
		]);
	}

	/**
	 * `BaseSmartAlbum::photos()` left-joins `photo_album` without any
	 * `album_id` restriction - a photo belonging to more than one regular
	 * album must still be counted exactly once here, not once per
	 * membership (see `ResolvesPhotoSource::resolvePhotoQuery()`'s
	 * `whereIn` id-subquery fix for this exact fan-out).
	 */
	public function testSmartAlbumDeduplicatesPhotoBelongingToMultipleAlbums(): void
	{
		$this->setInstanceDefaults('created_at', 'year');
		$this->photo1->is_highlighted = true;
		$this->photo1->save();
		// photo1 already belongs to album1 (base fixture) - attach it to a
		// second, same-owner album too.
		$this->photo1->albums()->attach($this->subAlbum1->id);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/highlighted/Photos/buckets');
		$this->assertOk($response);
		$json = $response->json();
		$this->assertSame([$this->photo1->created_at->format('Y')], $json['bucket_ids']);
		$this->assertSame([1], $json['counts']);
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

	// ── Feature 092: read-time ids over granularity-free keys ─────────────

	private function setPhotoLabelFormats(): void
	{
		foreach (['year' => 'Y', 'month' => 'Y-m', 'day' => 'Y-m-d', 'hour' => 'Y-m-d H:00'] as $precision => $format) {
			DB::table('configs')->where('key', '=', 'timeline_photo_date_format_' . $precision)->update(['value' => $format]);
		}
	}

	/**
	 * S-092-01: switching the album from day to hour granularity shows hour
	 * ids with hour labels, without rewriting any key.
	 */
	public function testGranularitySwitchNeedsNoRecompute(): void
	{
		$this->setInstanceDefaults('taken_at', 'year');
		$this->setPhotoLabelFormats();
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$album->photo_timeline = TimelinePhotoGranularity::DAY;
		$album->save();
		$p1 = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		$p2 = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		DB::table('photos')->where('id', '=', $p1->id)->update(['taken_key' => '2026-07-01-16']);
		DB::table('photos')->where('id', '=', $p2->id)->update(['taken_key' => '2026-07-01-09']);
		$this->recompute($album);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['2026-07-01'], 'counts' => [2], 'labels' => ['2026-07-01']]);

		$keys_before = DB::table('photo_album')->where('album_id', '=', $album->id)->orderBy('photo_id')->pluck('bucket_key')->all();
		$album->photo_timeline = TimelinePhotoGranularity::HOUR;
		$album->save();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['2026-07-01-09', '2026-07-01-16'], 'counts' => [1, 1], 'labels' => ['2026-07-01 09:00', '2026-07-01 16:00']]);
		$this->assertSame($keys_before, DB::table('photo_album')->where('album_id', '=', $album->id)->orderBy('photo_id')->pluck('bucket_key')->all());
	}

	/**
	 * S-092-03: a title date has no hour, so a title-sorted album at hour
	 * granularity shows day ids with day labels.
	 */
	public function testTitleDateAtHourGranularityShowsDays(): void
	{
		DB::table('configs')->where('key', '=', 'photo_title_bucket_mode')->update(['value' => 'date_prefix']);
		$this->setInstanceDefaults('title', 'hour');
		$this->setPhotoLabelFormats();
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->with_title('2024-03-15 Beach')->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->with_title('2024-03 Trip')->create();
		$this->recompute($album);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['2024-03-01', '2024-03-15'], 'labels' => ['2024-03-01', '2024-03-15']]);
	}

	/**
	 * S-092-11: an unvalidated photo of another owner is excluded for a
	 * guest and a non-owner, included for its owner and an admin.
	 */
	public function testUnvalidatedPhotoCountsPerViewer(): void
	{
		$this->setInstanceDefaults('is_highlighted', 'year');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		AccessPermission::factory()->public()->visible()->for_album($album)->create();
		AccessPermission::factory()->for_user($this->userMayUpload2)->for_album($album)->create();
		Photo::factory()->owned_by($this->userMayUpload2)->in($album)->create(['is_validated' => false]);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_validated' => true]);
		$this->recompute($album);

		$this->assertOk($guest = $this->getJsonV3("Albums/{$album->id}/Photos/buckets"));
		$guest->assertJson(['counts' => [1]]);
		$this->assertOk($non_owner = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets"));
		$non_owner->assertJson(['counts' => [1]]);
		$this->assertOk($owner = $this->actingAs($this->userMayUpload2)->getJsonV3("Albums/{$album->id}/Photos/buckets"));
		$owner->assertJson(['counts' => [2]]);
		$this->assertOk($admin = $this->actingAs($this->admin)->getJsonV3("Albums/{$album->id}/Photos/buckets"));
		$admin->assertJson(['counts' => [2]]);
	}

	/**
	 * S-092-15: the `unknown` bucket is last in both sort directions, for a
	 * stored and a computed source.
	 */
	public function testUnknownBucketIsLastInBothDirections(): void
	{
		$this->setInstanceDefaults('taken_at', 'year');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['taken_at' => new Carbon('2022-06-15')]);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['taken_at' => new Carbon('2023-06-15')]);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['taken_at' => null]);

		foreach ([OrderSortingType::ASC, OrderSortingType::DESC] as $order) {
			$album->photo_sorting = new PhotoSortingCriterion(ColumnSortingType::TAKEN_AT, $order);
			$album->save();
			$this->recompute($album);

			$expected = $order === OrderSortingType::ASC ? ['2022', '2023', 'unknown'] : ['2023', '2022', 'unknown'];
			$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
			$this->assertOk($response);
			$this->assertSame($expected, $response->json('bucket_ids'));
		}
	}

	/**
	 * @return array<string,array{0:string,1:array<string,string>,2:array<int,array<string,mixed>>,3:string[],4:int[]}>
	 */
	public static function computedSourceProvider(): array
	{
		return [
			'created_at' => ['created_at', ['timeline_photos_granularity' => 'month'], [
				['created_at' => '2023-05-01 10:00:00'], ['created_at' => '2023-05-20 10:00:00'], ['created_at' => '2024-01-02 10:00:00'],
			], ['2023-05', '2024-01'], [2, 1]],
			'taken_at' => ['taken_at', ['timeline_photos_granularity' => 'day'], [
				['taken_at' => new Carbon('2022-06-15 10:00:00')], ['taken_at' => new Carbon('2022-06-15 12:00:00')], ['taken_at' => null],
			], ['2022-06-15', 'unknown'], [2, 1]],
			'title date' => ['title', ['photo_title_bucket_mode' => 'date_prefix', 'timeline_photos_granularity' => 'year'], [
				['title' => '2024-03 A'], ['title' => '2024 B'], ['title' => 'No date'],
			], ['2024', 'unknown'], [2, 1]],
			'title alphabetical' => ['title', ['photo_title_bucket_mode' => 'alphabetical', 'photo_title_bucket_prefix_length' => '1'], [
				['title_base' => 'apple'], ['title_base' => 'avocado'], ['title_base' => 'banana'],
			], ['a', 'b'], [2, 1]],
			'highlighted' => ['is_highlighted', [], [
				['is_highlighted' => false], ['is_highlighted' => false], ['is_highlighted' => true],
			], ['0', '1'], [2, 1]],
			'type' => ['type', [], [
				['type' => 'image/jpeg'], ['type' => 'image/jpeg'], ['type' => 'video/mp4'],
			], ['image/jpeg', 'video/mp4'], [2, 1]],
			'rating' => ['rating_avg', [], [
				['rating_avg' => '4.5000'], ['rating_avg' => '5.0000'], ['rating_avg' => null],
			], ['5', 'unknown'], [2, 1]],
		];
	}

	/**
	 * S-092-05: a tag album computes ids in SQL; `ratios` and `details`
	 * windows return exactly each bucket's photos, with per-row ids equal to
	 * the window id.
	 *
	 * @param array<string,string>           $configs
	 * @param array<int,array<string,mixed>> $photos
	 * @param string[]                       $expected_ids
	 * @param int[]                          $expected_counts
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider('computedSourceProvider')]
	public function testTagAlbumTiersAgreePerSortColumn(string $column, array $configs, array $photos, array $expected_ids, array $expected_counts): void
	{
		$this->setInstanceDefaults($column, 'year');
		DB::table('configs')->where('key', '=', 'sorting_photos_order')->update(['value' => 'ASC']);
		foreach ($configs as $key => $value) {
			DB::table('configs')->where('key', '=', $key)->update(['value' => $value]);
		}
		$tag = Tag::factory()->with_name('feature_092_tag')->create();
		$tag_album = TagAlbum::factory()->owned_by($this->userMayUpload1)->of_tags([$tag])->create();
		foreach ($photos as $attributes) {
			Photo::factory()->owned_by($this->userMayUpload1)->with_tags([$tag])->create($attributes);
		}

		$this->assertTiersAgree($tag_album->id, $expected_ids, $expected_counts);
	}

	/**
	 * S-092-06: a smart album with the instance default sort.
	 */
	public function testSmartAlbumTiersAgree(): void
	{
		$this->setInstanceDefaults('created_at', 'month');
		DB::table('configs')->where('key', '=', 'sorting_photos_order')->update(['value' => 'DESC']);
		foreach (['2023-05-01 10:00:00', '2023-05-20 10:00:00', '2024-01-02 10:00:00'] as $created_at) {
			Photo::factory()->owned_by($this->userMayUpload1)->create(['created_at' => $created_at, 'is_highlighted' => true]);
		}

		$this->assertTiersAgree('highlighted', ['2024-01', '2023-05'], [1, 2]);
	}

	/**
	 * @param string[] $expected_ids
	 * @param int[]    $expected_counts
	 */
	private function assertTiersAgree(string $album_id, array $expected_ids, array $expected_counts): void
	{
		$buckets = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album_id}/Photos/buckets");
		$this->assertOk($buckets);
		self::assertSame($expected_ids, $buckets->json('bucket_ids'));
		self::assertSame($expected_counts, $buckets->json('counts'));

		// Grouping the whole-scope rows by id reproduces the buckets tier.
		$whole = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album_id}/Photos");
		$this->assertOk($whole);
		$runs = [];
		foreach ($whole->json('bucket_ids') as $bucket_id) {
			$last = array_key_last($runs);
			if ($last !== null && $runs[$last][0] === $bucket_id) {
				$runs[$last][1]++;
			} else {
				$runs[] = [$bucket_id, 1];
			}
		}
		self::assertSame($expected_ids, array_column($runs, 0));
		self::assertSame($expected_counts, array_column($runs, 1));

		foreach ($expected_ids as $i => $bucket_id) {
			$ratios = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album_id}/Photos", ['bucket_ids' => [$bucket_id]]);
			$this->assertOk($ratios);
			self::assertSame(array_fill(0, $expected_counts[$i], $bucket_id), $ratios->json('bucket_ids'));

			$details = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album_id}/Photos/details", ['bucket_id' => $bucket_id]);
			$this->assertOk($details);
			self::assertEqualsCanonicalizing($ratios->json('ids'), $details->json('ids'));
		}
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

	/**
	 * A `TagAlbum`'s photo listing has no `photo_album` row to invalidate
	 * against (see `ResolvesPhotoSource`) - a warm cache must still reflect
	 * a photo gaining a matching tag, via `ManagedCachePhotoListingInvalidator::handlePhotoTagsChanged()`.
	 */
	public function testCacheInvalidatedOnPhotoTagsChanged(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$tag = Tag::factory()->with_name('cache_regression_tag')->create();
		$tag_album = TagAlbum::factory()->owned_by($this->userMayUpload1)->of_tags([$tag])->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->create();

		// Warm the cache while the photo does not yet carry the tag.
		$before = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$tag_album->id}/Photos/buckets")->assertOk()->json('counts');
		self::assertSame([], $before);

		$this->actingAs($this->userMayUpload1)->patchJson('Photo::tags', [
			'photo_ids' => [$photo->id],
			'tags' => ['cache_regression_tag'],
			'shall_override' => true,
		])->assertNoContent();

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$tag_album->id}/Photos/buckets")->assertOk()->json('counts');
		self::assertSame([1], $after);
	}

	/**
	 * Same as above, for `PersonAlbum` -
	 * `ManagedCachePhotoListingInvalidator::handlePhotoPersonsChanged()`.
	 */
	public function testCacheInvalidatedOnPhotoPersonsChanged(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');
		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'private');

		$person = Person::factory()->create(['name' => 'CacheRegressionPerson', 'is_searchable' => true]);
		$create_response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'person_album_cache_regression',
			'persons' => [$person->id],
			'is_and' => false,
		]);
		$this->assertOk($create_response);
		$person_album_id = $create_response->getOriginalContent();

		$photo = Photo::factory()->owned_by($this->userMayUpload1)->create();
		$face = Face::factory()->for_photo($photo)->create();

		// Warm the cache while the face is not yet assigned to the person.
		$before = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$person_album_id}/Photos/buckets")->assertOk()->json('counts');
		self::assertSame([], $before);

		$this->actingAs($this->userMayUpload1)->postJson("Face/{$face->id}/assign", [
			'person_id' => $person->id,
		])->assertCreated();

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$person_album_id}/Photos/buckets")->assertOk()->json('counts');
		self::assertSame([1], $after);
	}

	/**
	 * A rating-dependent smart album (`five_stars`) - `PhotoRatingChanged`
	 * carries no rating value, so
	 * `ManagedCachePhotoListingInvalidator::handlePhotoRatingChanged()`
	 * evicts every rating-dependent smart album unconditionally.
	 */
	public function testCacheInvalidatedOnPhotoRatingChanged(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');
		Configs::set('rating_enabled', '1');

		$photo = Photo::factory()->owned_by($this->userMayUpload1)->create();

		// Warm the cache while the photo is unrated.
		$before = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/five_stars/Photos/buckets')->assertOk()->json('counts');
		self::assertSame([], $before);

		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $photo->id,
			'rating' => 5,
		])->assertCreated();

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/five_stars/Photos/buckets')->assertOk()->json('counts');
		self::assertSame([1], $after);
	}

	/**
	 * The `highlighted` smart album -
	 * `ManagedCachePhotoListingInvalidator::handlePhotoHighlightToggled()`.
	 */
	public function testCacheInvalidatedOnPhotoHighlightToggled(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$photo = Photo::factory()->owned_by($this->userMayUpload1)->create();

		// Warm the cache while the photo is not yet highlighted.
		$before = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/highlighted/Photos/buckets')->assertOk()->json('counts');
		self::assertSame([], $before);

		$this->actingAs($this->userMayUpload1)->postJson('Photo::highlight', [
			'photo_ids' => [$photo->id],
			'is_highlighted' => true,
		])->assertNoContent();

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/highlighted/Photos/buckets')->assertOk()->json('counts');
		self::assertSame([1], $after);
	}
}
