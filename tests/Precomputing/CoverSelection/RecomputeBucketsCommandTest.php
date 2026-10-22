<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\CoverSelection;

use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\User;
use App\Services\TitleSplitter;
use Illuminate\Support\Facades\DB;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test the `lychee:recompute-buckets` command: every key in the
 * granularity-free format (FR-092-15, S-092-17), chunked (FR-092-14).
 */
class RecomputeBucketsCommandTest extends BasePrecomputingTest
{
	private function setAlbumInstanceDefaults(string $sorting_col = 'created_at', string $granularity = 'year'): void
	{
		Configs::set('sorting_albums_col', $sorting_col);
		Configs::set('timeline_albums_granularity', $granularity);
	}

	private function setPhotoInstanceDefaults(string $sorting_col = 'created_at', string $granularity = 'year'): void
	{
		Configs::set('sorting_photos_col', $sorting_col);
		Configs::set('timeline_photos_granularity', $granularity);
	}

	public function testCommandRecomputesBucketKeyForEveryAlbumAgainstAFixture(): void
	{
		$this->setAlbumInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$root = Album::factory()->as_root()->owned_by($user)->create();
		$child1 = Album::factory()->children_of($root)->owned_by($user)->create();
		$child2 = Album::factory()->children_of($root)->owned_by($user)->create();

		// bucket_key starts unpopulated (never ran RecomputeAlbumStatsJob).
		$this->assertNull($child1->fresh()->bucket_key);
		$this->assertNull($child2->fresh()->bucket_key);

		$this->artisan('lychee:recompute-buckets')->assertExitCode(0);

		$this->assertSame($child1->created_at->copy()->utc()->format('Y-m-d'), $child1->fresh()->bucket_key);
		$this->assertSame($child2->created_at->copy()->utc()->format('Y-m-d'), $child2->fresh()->bucket_key);
		// Root album itself has no parent -> governed by the same instance-wide default.
		$this->assertSame($root->created_at->copy()->utc()->format('Y-m-d'), $root->fresh()->bucket_key);
	}

	public function testCommandRecomputesBucketKeyForEveryPhotoAlbumRowAgainstAFixture(): void
	{
		$this->setPhotoInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$photo1 = Photo::factory()->owned_by($user)->in($album)->create();
		$photo2 = Photo::factory()->owned_by($user)->in($album)->create();

		// bucket_key starts unpopulated (attached directly via factory, bypassing SetParent).
		$this->assertNull(DB::table('photo_album')->where('photo_id', '=', $photo1->id)->first()->bucket_key);
		$this->assertNull(DB::table('photo_album')->where('photo_id', '=', $photo2->id)->first()->bucket_key);

		$this->artisan('lychee:recompute-buckets')->assertExitCode(0);

		$this->assertSame($photo1->created_at->copy()->utc()->format('Y-m-d-H'), DB::table('photo_album')->where('photo_id', '=', $photo1->id)->first()->bucket_key);
		$this->assertSame($photo2->created_at->copy()->utc()->format('Y-m-d-H'), DB::table('photo_album')->where('photo_id', '=', $photo2->id)->first()->bucket_key);
	}

	public function testCommandIssuesZeroQueriesAgainstSizeVariantsOrTags(): void
	{
		$this->setAlbumInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$this->setPhotoInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		Photo::factory()->owned_by($user)->in($album)->create();

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->artisan('lychee:recompute-buckets')->assertExitCode(0);
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$forbidden_queries = array_filter($log, fn (array $q) => preg_match('/\b(size_variants|tags|photos_tags)\b/i', str_replace(['"', '`'], '', $q['query'])) === 1);
		$this->assertCount(0, $forbidden_queries, 'Expected zero size_variants/tags queries, got: ' . implode(' | ', array_column($forbidden_queries, 'query')));
	}

	public function testCommandHandlesEmptyTables(): void
	{
		$this->artisan('lychee:recompute-buckets')->assertExitCode(0);
		$this->assertTrue(true);
	}

	public function testRerunAfterAlbumTitleBucketModeConfigChangeUpdatesExistingRows(): void
	{
		Configs::set('title_bucket_mode', 'date_prefix');
		$this->setAlbumInstanceDefaults(sorting_col: 'title', granularity: 'year');
		$user = User::factory()->create();
		$root = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->children_of($root)->owned_by($user)->with_title('Vacation Photos')->create();
		$child->title_base = TitleSplitter::split($child->title)->base;
		$child->save();

		$this->artisan('lychee:recompute-buckets')->assertExitCode(0);
		// date_prefix mode, unparseable title -> unknown/null.
		$this->assertNull($child->fresh()->bucket_key);

		Configs::set('title_bucket_mode', 'alphabetical');
		Configs::set('title_bucket_prefix_length', '1');

		$this->artisan('lychee:recompute-buckets')->assertExitCode(0);
		// The whole title base: the prefix length applies at read time.
		$this->assertSame('vacation photos', $child->fresh()->bucket_key);
	}

	public function testRerunAfterPhotoTitleBucketModeConfigChangeUpdatesExistingRows(): void
	{
		Configs::set('photo_title_bucket_mode', 'date_prefix');
		$this->setPhotoInstanceDefaults(sorting_col: 'title', granularity: 'year');
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$photo = Photo::factory()->owned_by($user)->in($album)->with_title('Vacation Photos')->create();
		$photo->title_base = TitleSplitter::split($photo->title)->base;
		$photo->save();

		$this->artisan('lychee:recompute-buckets')->assertExitCode(0);
		// date_prefix mode, unparseable title -> null/"unknown".
		$this->assertNull(DB::table('photo_album')->where('photo_id', '=', $photo->id)->first()->bucket_key);

		Configs::set('photo_title_bucket_mode', 'alphabetical');
		Configs::set('photo_title_bucket_prefix_length', '1');

		$this->artisan('lychee:recompute-buckets')->assertExitCode(0);
		$this->assertSame('vacation photos', DB::table('photo_album')->where('photo_id', '=', $photo->id)->first()->bucket_key);
	}

	/**
	 * A photo linked into two albums with different sort columns recomputes
	 * to two different keys in a single full-table pass.
	 */
	public function testFullTablePassRespectsPerAlbumDivergentSettings(): void
	{
		$this->setPhotoInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album_a = Album::factory()->as_root()->owned_by($user)->create();
		$album_b = Album::factory()->as_root()->owned_by($user)->create();
		$album_b->photo_sorting = new \App\DTO\PhotoSortingCriterion(\App\Enum\ColumnSortingType::TYPE, \App\Enum\OrderSortingType::ASC);
		$album_b->save();

		$photo = Photo::factory()->owned_by($user)->create();
		DB::table('photo_album')->insert(['photo_id' => $photo->id, 'album_id' => $album_a->id]);
		DB::table('photo_album')->insert(['photo_id' => $photo->id, 'album_id' => $album_b->id]);

		$this->artisan('lychee:recompute-buckets')->assertExitCode(0);

		$row_a = DB::table('photo_album')->where('photo_id', '=', $photo->id)->where('album_id', '=', $album_a->id)->first();
		$row_b = DB::table('photo_album')->where('photo_id', '=', $photo->id)->where('album_id', '=', $album_b->id)->first();

		$this->assertSame($photo->created_at->copy()->utc()->format('Y-m-d-H'), $row_a->bucket_key);
		$this->assertSame('image/jpeg', $row_b->bucket_key);
	}

	/**
	 * With one row per page, the keyset cursor lands between the two pivot
	 * rows of a photo linked into two albums, and every row across several
	 * photos is still visited exactly once.
	 */
	public function testKeysetPaginationVisitsEveryRowAcrossPageBoundaries(): void
	{
		$this->setPhotoInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album_a = Album::factory()->as_root()->owned_by($user)->create();
		$album_b = Album::factory()->as_root()->owned_by($user)->create();

		$shared = Photo::factory()->owned_by($user)->create();
		DB::table('photo_album')->insert(['photo_id' => $shared->id, 'album_id' => $album_a->id]);
		DB::table('photo_album')->insert(['photo_id' => $shared->id, 'album_id' => $album_b->id]);
		$photo1 = Photo::factory()->owned_by($user)->in($album_a)->create();
		$photo2 = Photo::factory()->owned_by($user)->in($album_b)->create();

		$this->artisan('lychee:recompute-buckets', ['--chunk' => 1])->assertExitCode(0);

		$this->assertSame(0, DB::table('photo_album')->whereNull('bucket_key')->count());
		foreach ([$shared, $photo1, $photo2] as $photo) {
			$keys = DB::table('photo_album')->where('photo_id', '=', $photo->id)->pluck('bucket_key')->all();
			foreach ($keys as $key) {
				$this->assertSame($photo->created_at->copy()->utc()->format('Y-m-d-H'), $key);
			}
		}
	}
}
