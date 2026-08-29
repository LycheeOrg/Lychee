<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\CoverSelection;

use App\DTO\AlbumSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Enum\TimelineAlbumGranularity;
use App\Jobs\RecomputeAlbumStatsJob;
use App\Jobs\RecomputeChildAlbumBucketsJob;
use App\Models\Album;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test RecomputeChildAlbumBucketsJob (Feature 061, FR-061-03).
 */
class RecomputeChildAlbumBucketsJobTest extends BasePrecomputingTest
{
	private function setInstanceDefaults(string $sorting_col = 'created_at', string $granularity = 'year'): void
	{
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update(['value' => $sorting_col]);
		DB::table('configs')->where('key', '=', 'timeline_albums_granularity')->update(['value' => $granularity]);
	}

	/** @return string[] */
	private function makeThreeChildren(User $user, Album $parent): array
	{
		$ids = [];
		foreach (range(1, 3) as $_) {
			$child = Album::factory()->children_of($parent)->owned_by($user)->create();
			(new RecomputeAlbumStatsJob($child->id, propagate_to_parent: false))->handle();
			$ids[] = $child->id;
		}

		return $ids;
	}

	public function testAlbumTimelineChangeRecomputesAllDirectChildren(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child_ids = $this->makeThreeChildren($user, $parent);

		foreach ($child_ids as $id) {
			$this->assertSame(Album::find($id)->created_at->format('Y'), Album::find($id)->bucket_id);
		}

		$parent->album_timeline = TimelineAlbumGranularity::MONTH;
		$parent->save();

		(new RecomputeChildAlbumBucketsJob($parent->id))->handle();

		foreach ($child_ids as $id) {
			$child = Album::find($id);
			$this->assertSame($child->created_at->format('Y-m'), $child->bucket_id);
		}
	}

	public function testAlbumSortingColChangeRecomputesAllDirectChildren(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child_ids = $this->makeThreeChildren($user, $parent);

		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::OWNER_ID, OrderSortingType::ASC);
		$parent->save();

		(new RecomputeChildAlbumBucketsJob($parent->id))->handle();

		foreach ($child_ids as $id) {
			$this->assertNull(Album::find($id)->bucket_id);
		}
	}

	public function testZeroChildrenParentIsNoOp(): void
	{
		$this->setInstanceDefaults();
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();

		// Must not throw.
		(new RecomputeChildAlbumBucketsJob($parent->id))->handle();
		$this->assertTrue(true);
	}

	public function testRecomputesInOneBulkUpdateNotOnePerChild(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$this->makeThreeChildren($user, $parent);

		$parent->album_timeline = TimelineAlbumGranularity::MONTH;
		$parent->save();

		DB::flushQueryLog();
		DB::enableQueryLog();
		(new RecomputeChildAlbumBucketsJob($parent->id))->handle();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$write_queries = array_filter($log, fn (array $q) => preg_match('/^(update|insert)/i', trim($q['query'])) === 1);
		$this->assertCount(1, $write_queries, 'Expected exactly one bulk write query, got: ' . implode(' | ', array_column($write_queries, 'query')));
	}
}
