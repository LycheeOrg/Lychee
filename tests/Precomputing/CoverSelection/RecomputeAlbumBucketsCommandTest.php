<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\CoverSelection;

use App\Models\Album;
use App\Models\User;
use App\Services\TitleSplitter;
use Illuminate\Support\Facades\DB;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test the `lychee:recompute-album-buckets` backfill command (Feature 061,
 * FR-061-04, CLI-061-01, NFR-061-03).
 */
class RecomputeAlbumBucketsCommandTest extends BasePrecomputingTest
{
	private function setInstanceDefaults(string $sorting_col = 'created_at', string $granularity = 'year'): void
	{
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update(['value' => $sorting_col]);
		DB::table('configs')->where('key', '=', 'timeline_albums_granularity')->update(['value' => $granularity]);
	}

	public function testCommandRecomputesBucketIdForEveryAlbumAgainstAFixture(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$root = Album::factory()->as_root()->owned_by($user)->create();
		$child1 = Album::factory()->children_of($root)->owned_by($user)->create();
		$child2 = Album::factory()->children_of($root)->owned_by($user)->create();

		// bucket_id starts unpopulated (never ran RecomputeAlbumStatsJob).
		$this->assertNull($child1->fresh()->bucket_id);
		$this->assertNull($child2->fresh()->bucket_id);

		$this->artisan('lychee:recompute-album-buckets')->assertExitCode(0);

		$expected = $root->created_at->format('Y');
		$this->assertSame($expected, $child1->fresh()->bucket_id);
		$this->assertSame($expected, $child2->fresh()->bucket_id);
		// Root album itself has no parent -> governed by the same instance-wide default.
		$this->assertSame($root->created_at->format('Y'), $root->fresh()->bucket_id);
	}

	public function testCommandIssuesZeroQueriesAgainstPhotos(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$root = Album::factory()->as_root()->owned_by($user)->create();
		Album::factory()->children_of($root)->owned_by($user)->create();

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->artisan('lychee:recompute-album-buckets')->assertExitCode(0);
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$photo_queries = array_filter($log, fn (array $q) => preg_match('/\b(photos|photo_album)\b/i', str_replace(['"', '`'], '', $q['query'])) === 1);
		$this->assertCount(0, $photo_queries, 'Expected zero photos-table queries, got: ' . implode(' | ', array_column($photo_queries, 'query')));
	}

	public function testRerunAfterTitleBucketModeConfigChangeUpdatesExistingRows(): void
	{
		DB::table('configs')->where('key', '=', 'title_bucket_mode')->update(['value' => 'date_prefix']);
		$this->setInstanceDefaults(sorting_col: 'title', granularity: 'year');
		$user = User::factory()->create();
		$root = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->children_of($root)->owned_by($user)->with_title('Vacation Photos')->create();
		$child->title_base = TitleSplitter::split($child->title)->base;
		$child->save();

		$this->artisan('lychee:recompute-album-buckets')->assertExitCode(0);
		// date_prefix mode, unparseable title -> unknown/null.
		$this->assertNull($child->fresh()->bucket_id);

		DB::table('configs')->where('key', '=', 'title_bucket_mode')->update(['value' => 'alphabetical']);
		DB::table('configs')->where('key', '=', 'title_bucket_prefix_length')->update(['value' => '1']);

		$this->artisan('lychee:recompute-album-buckets')->assertExitCode(0);
		$this->assertSame('v', $child->fresh()->bucket_id);
	}
}
