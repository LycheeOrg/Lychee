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
use App\Jobs\RecomputeAlbumStatsJob;
use App\Jobs\RecomputeChildAlbumBucketsJob;
use App\Models\Album;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test RecomputeChildAlbumBucketsJob (FR-092-04, FR-092-12, FR-092-14).
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

	public function testParentSortColumnChangeRecomputesAllDirectChildren(): void
	{
		DB::table('configs')->where('key', '=', 'title_bucket_mode')->update(['value' => 'date_prefix']);
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child_ids = $this->makeThreeChildren($user, $parent);
		DB::table('base_albums')->whereIn('id', $child_ids)->update(['title' => '2022-07 Vacation']);

		foreach ($child_ids as $id) {
			$this->assertSame(Album::find($id)->created_at->copy()->utc()->format('Y-m-d'), Album::find($id)->bucket_key);
		}

		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::TITLE, OrderSortingType::ASC);
		$parent->save();

		(new RecomputeChildAlbumBucketsJob($parent->id))->handle();

		foreach ($child_ids as $id) {
			$this->assertSame('2022-07-01', Album::find($id)->bucket_key);
		}
	}

	/**
	 * Only the parent's effective sort column decides the key: a parent
	 * without both column and order follows the instance default.
	 */
	public function testParentWithoutOrderFollowsInstanceDefault(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child_ids = $this->makeThreeChildren($user, $parent);
		DB::table('albums')->where('id', '=', $parent->id)->update(['album_sorting_col' => 'owner_id', 'album_sorting_order' => null]);

		(new RecomputeChildAlbumBucketsJob($parent->id))->handle();

		foreach ($child_ids as $id) {
			$this->assertSame(Album::find($id)->created_at->copy()->utc()->format('Y-m-d'), Album::find($id)->bucket_key);
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
			$this->assertNull(Album::find($id)->bucket_key);
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
