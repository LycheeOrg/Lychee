<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\CoverSelection;

use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use App\Services\TitleSplitter;
use Illuminate\Support\Facades\DB;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test the `lychee:recompute-photo-buckets` backfill command.
 */
class RecomputePhotoBucketsCommandTest extends BasePrecomputingTest
{
	private function setInstanceDefaults(string $sorting_col = 'created_at', string $granularity = 'year'): void
	{
		DB::table('configs')->where('key', '=', 'sorting_photos_col')->update(['value' => $sorting_col]);
		DB::table('configs')->where('key', '=', 'timeline_photos_granularity')->update(['value' => $granularity]);
	}

	public function testCommandRecomputesBucketIdForEveryPhotoAlbumRowAgainstAFixture(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$photo1 = Photo::factory()->owned_by($user)->in($album)->create();
		$photo2 = Photo::factory()->owned_by($user)->in($album)->create();

		// bucket_id starts unpopulated (attached directly via factory, bypassing SetParent).
		$this->assertNull(DB::table('photo_album')->where('photo_id', '=', $photo1->id)->first()->bucket_id);
		$this->assertNull(DB::table('photo_album')->where('photo_id', '=', $photo2->id)->first()->bucket_id);

		$this->artisan('lychee:recompute-photo-buckets')->assertExitCode(0);

		$expected1 = $photo1->created_at->format('Y');
		$expected2 = $photo2->created_at->format('Y');
		$this->assertSame($expected1, DB::table('photo_album')->where('photo_id', '=', $photo1->id)->first()->bucket_id);
		$this->assertSame($expected2, DB::table('photo_album')->where('photo_id', '=', $photo2->id)->first()->bucket_id);
	}

	public function testCommandIssuesZeroQueriesAgainstSizeVariantsOrTags(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		Photo::factory()->owned_by($user)->in($album)->create();

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->artisan('lychee:recompute-photo-buckets')->assertExitCode(0);
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$forbidden_queries = array_filter($log, fn (array $q) => preg_match('/\b(size_variants|tags|photos_tags)\b/i', str_replace(['"', '`'], '', $q['query'])) === 1);
		$this->assertCount(0, $forbidden_queries, 'Expected zero size_variants/tags queries, got: ' . implode(' | ', array_column($forbidden_queries, 'query')));
	}

	public function testCommandHandlesEmptyPhotoAlbumTable(): void
	{
		$this->artisan('lychee:recompute-photo-buckets')->assertExitCode(0);
		$this->assertTrue(true);
	}

	public function testRerunAfterTitleBucketModeConfigChangeUpdatesExistingRows(): void
	{
		DB::table('configs')->where('key', '=', 'photo_title_bucket_mode')->update(['value' => 'date_prefix']);
		$this->setInstanceDefaults(sorting_col: 'title', granularity: 'year');
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$photo = Photo::factory()->owned_by($user)->in($album)->with_title('Vacation Photos')->create();
		$photo->title_base = TitleSplitter::split($photo->title)->base;
		$photo->save();

		$this->artisan('lychee:recompute-photo-buckets')->assertExitCode(0);
		// date_prefix mode, unparseable title -> null/"unknown".
		$this->assertNull(DB::table('photo_album')->where('photo_id', '=', $photo->id)->first()->bucket_id);

		DB::table('configs')->where('key', '=', 'photo_title_bucket_mode')->update(['value' => 'alphabetical']);
		DB::table('configs')->where('key', '=', 'photo_title_bucket_prefix_length')->update(['value' => '1']);

		$this->artisan('lychee:recompute-photo-buckets')->assertExitCode(0);
		$this->assertSame('v', DB::table('photo_album')->where('photo_id', '=', $photo->id)->first()->bucket_id);
	}

	/**
	 * A photo linked into two albums with different effective settings
	 * recomputes to two different `bucket_id` values in a single full-table
	 * pass.
	 */
	public function testFullTablePassRespectsPerAlbumDivergentSettings(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album_a = Album::factory()->as_root()->owned_by($user)->create();
		$album_b = Album::factory()->as_root()->owned_by($user)->create();
		$album_a->photo_timeline = \App\Enum\TimelinePhotoGranularity::YEAR;
		$album_a->save();
		$album_b->photo_timeline = \App\Enum\TimelinePhotoGranularity::MONTH;
		$album_b->save();

		$photo = Photo::factory()->owned_by($user)->create();
		DB::table('photo_album')->insert(['photo_id' => $photo->id, 'album_id' => $album_a->id]);
		DB::table('photo_album')->insert(['photo_id' => $photo->id, 'album_id' => $album_b->id]);

		$this->artisan('lychee:recompute-photo-buckets')->assertExitCode(0);

		$row_a = DB::table('photo_album')->where('photo_id', '=', $photo->id)->where('album_id', '=', $album_a->id)->first();
		$row_b = DB::table('photo_album')->where('photo_id', '=', $photo->id)->where('album_id', '=', $album_b->id)->first();

		$this->assertSame($photo->created_at->format('Y'), $row_a->bucket_id);
		$this->assertSame($photo->created_at->format('Y-m'), $row_b->bucket_id);
	}
}
