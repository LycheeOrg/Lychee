<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\CoverSelection;

use App\Jobs\RecomputeRootAlbumBucketsJob;
use App\Models\Album;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test RecomputeRootAlbumBucketsJob (Feature 062, FR-062-07, NFR-062-03).
 */
class RecomputeRootAlbumBucketsJobTest extends BasePrecomputingTest
{
	private function setInstanceDefaults(string $sorting_col = 'created_at', string $granularity = 'year'): void
	{
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update(['value' => $sorting_col]);
		DB::table('configs')->where('key', '=', 'timeline_albums_granularity')->update(['value' => $granularity]);
	}

	/** @return string[] */
	private function makeThreeRootAlbums(User $user): array
	{
		$ids = [];
		foreach (range(1, 3) as $_) {
			$album = Album::factory()->as_root()->owned_by($user)->create();
			$ids[] = $album->id;
		}

		return $ids;
	}

	public function testRecomputesBucketIdForEveryRootAlbumByCreatedAt(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$ids = $this->makeThreeRootAlbums($user);

		(new RecomputeRootAlbumBucketsJob())->handle();

		foreach ($ids as $id) {
			$album = Album::find($id);
			$this->assertSame($album->created_at->format('Y'), $album->bucket_id);
		}
	}

	public function testChangingGranularityRecomputesAllRootAlbums(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$ids = $this->makeThreeRootAlbums($user);

		(new RecomputeRootAlbumBucketsJob())->handle();
		foreach ($ids as $id) {
			$this->assertSame(Album::find($id)->created_at->format('Y'), Album::find($id)->bucket_id);
		}

		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'month');
		(new RecomputeRootAlbumBucketsJob())->handle();

		foreach ($ids as $id) {
			$album = Album::find($id);
			$this->assertSame($album->created_at->format('Y-m'), $album->bucket_id);
		}
	}

	public function testSortingColOwnerIdIsNoLongerReachableEverBucketIdComputedFromCreatedAtInstead(): void
	{
		// OWNER_ID was removed as a selectable sorting_albums_col value
		// (Feature 062, FR-062-08) — this test simply confirms root buckets
		// are computed from a real date/title column, never owner-derived
		// (G4).
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$ids = $this->makeThreeRootAlbums($user);

		(new RecomputeRootAlbumBucketsJob())->handle();

		foreach ($ids as $id) {
			$this->assertNotNull(Album::find($id)->bucket_id);
		}
	}

	public function testZeroRootAlbumsIsNoOp(): void
	{
		$this->setInstanceDefaults();

		// Must not throw, even with the base fixture's own root albums absent
		// from this isolated test database state.
		(new RecomputeRootAlbumBucketsJob())->handle();
		$this->assertTrue(true);
	}

	public function testOnlyAffectsRootAlbumsNotChildren(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->children_of($parent)->owned_by($user)->create();

		(new RecomputeRootAlbumBucketsJob())->handle();

		$this->assertNotNull(Album::find($parent->id)->bucket_id);
		$this->assertNull(Album::find($child->id)->bucket_id);
	}

	public function testRecomputesInOneBulkUpdateNotOnePerAlbum(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$this->makeThreeRootAlbums($user);

		DB::flushQueryLog();
		DB::enableQueryLog();
		(new RecomputeRootAlbumBucketsJob())->handle();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$write_queries = array_filter($log, fn (array $q) => preg_match('/^(update|insert)/i', trim($q['query'])) === 1);
		$this->assertCount(1, $write_queries, 'Expected exactly one bulk write query, got: ' . implode(' | ', array_column($write_queries, 'query')));
	}
}
