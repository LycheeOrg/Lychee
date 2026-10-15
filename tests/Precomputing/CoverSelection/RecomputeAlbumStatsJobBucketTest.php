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
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use App\Services\TitleSplitter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test RecomputeAlbumStatsJob::computeBucketKey(): the granularity-free key
 * of the parent's effective sort column (FR-092-04). Granularity and
 * prefix length never shape it.
 */
class RecomputeAlbumStatsJobBucketTest extends BasePrecomputingTest
{
	private function setInstanceDefaults(string $sorting_col = 'created_at', string $granularity = 'year'): void
	{
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update(['value' => $sorting_col]);
		DB::table('configs')->where('key', '=', 'timeline_albums_granularity')->update(['value' => $granularity]);
	}

	// ─── T-061-02: parent-governed source + granularity resolution ─────────

	public function testRootAlbumUsesInstanceWideDefaults(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();

		$album->refresh();
		$this->assertSame($album->created_at->copy()->utc()->format('Y-m-d'), $album->bucket_key);
	}

	public function testNonRootAlbumUsesParentsExplicitOverrides(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::ASC);
		$parent->album_timeline = TimelineAlbumGranularity::MONTH;
		$parent->save();

		$child = Album::factory()->children_of($parent)->owned_by($user)->create();

		(new RecomputeAlbumStatsJob($child->id, propagate_to_parent: false))->handle();

		$child->refresh();
		$this->assertSame($child->created_at->copy()->utc()->format('Y-m-d'), $child->bucket_key);
	}

	public function testNonRootAlbumWithParentOnDefaultGranularityFallsBackToInstanceWide(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'day');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		// No explicit album_sorting/album_timeline overrides on the parent.
		$child = Album::factory()->children_of($parent)->owned_by($user)->create();

		(new RecomputeAlbumStatsJob($child->id, propagate_to_parent: false))->handle();

		$child->refresh();
		$this->assertSame($child->created_at->copy()->utc()->format('Y-m-d'), $child->bucket_key);
	}

	public function testOwnerIdParentSortColumnAlwaysYieldsNullBucket(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::OWNER_ID, OrderSortingType::ASC);
		$parent->save();

		$child = Album::factory()->children_of($parent)->owned_by($user)->create();

		(new RecomputeAlbumStatsJob($child->id, propagate_to_parent: false))->handle();

		$child->refresh();
		$this->assertNull($child->bucket_key);
	}

	// ─── T-061-03: truncation correctness + NULL cases ──────────────────────

	public function testMinTakenAtBucketing(): void
	{
		$this->setInstanceDefaults(sorting_col: 'min_taken_at', granularity: 'month');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->children_of($parent)->owned_by($user)->create();

		$photo = Photo::factory()->owned_by($user)->create(['taken_at' => new Carbon('2024-03-15 10:00:00')]);
		$photo->albums()->attach($child->id);

		(new RecomputeAlbumStatsJob($child->id, propagate_to_parent: false))->handle();

		$child->refresh();
		$this->assertSame('2024-03-15', $child->bucket_key);
	}

	public function testMaxTakenAtBucketing(): void
	{
		$this->setInstanceDefaults(sorting_col: 'max_taken_at', granularity: 'day');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->children_of($parent)->owned_by($user)->create();

		$photo = Photo::factory()->owned_by($user)->create(['taken_at' => new Carbon('2024-03-15 10:00:00')]);
		$photo->albums()->attach($child->id);

		(new RecomputeAlbumStatsJob($child->id, propagate_to_parent: false))->handle();

		$child->refresh();
		$this->assertSame('2024-03-15', $child->bucket_key);
	}

	public function testNoDatedPhotosYieldsNullBucketForMinTakenAt(): void
	{
		$this->setInstanceDefaults(sorting_col: 'min_taken_at', granularity: 'year');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->children_of($parent)->owned_by($user)->create();

		(new RecomputeAlbumStatsJob($child->id, propagate_to_parent: false))->handle();

		$child->refresh();
		$this->assertNull($child->bucket_key);
	}

	public function testTitleDatePrefixModeParsesLeadingDate(): void
	{
		DB::table('configs')->where('key', '=', 'title_bucket_mode')->update(['value' => 'date_prefix']);
		$this->setInstanceDefaults(sorting_col: 'title', granularity: 'month');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->children_of($parent)->owned_by($user)->with_title('2022-07 Vacation')->create();

		(new RecomputeAlbumStatsJob($child->id, propagate_to_parent: false))->handle();

		$child->refresh();
		$this->assertSame('2022-07-01', $child->bucket_key);
	}

	public function testTitleDatePrefixModeUnparseableTitleYieldsNull(): void
	{
		DB::table('configs')->where('key', '=', 'title_bucket_mode')->update(['value' => 'date_prefix']);
		$this->setInstanceDefaults(sorting_col: 'title', granularity: 'year');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->children_of($parent)->owned_by($user)->with_title('Vacation Photos')->create();

		(new RecomputeAlbumStatsJob($child->id, propagate_to_parent: false))->handle();

		$child->refresh();
		$this->assertNull($child->bucket_key);
	}

	public function testTitleAlphabeticalModeUsesTitleBasePrefix(): void
	{
		DB::table('configs')->where('key', '=', 'title_bucket_mode')->update(['value' => 'alphabetical']);
		DB::table('configs')->where('key', '=', 'title_bucket_prefix_length')->update(['value' => '1']);
		$this->setInstanceDefaults(sorting_col: 'title', granularity: 'year');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->children_of($parent)->owned_by($user)->with_title('Vacation Photos')->create();
		$child->title_base = TitleSplitter::split($child->title)->base;
		$child->save();

		(new RecomputeAlbumStatsJob($child->id, propagate_to_parent: false))->handle();

		$child->refresh();
		$this->assertSame('vacation photos', $child->bucket_key);
	}

	public function testTitleAlphabeticalModeNeverNullEvenForUnparseableTitle(): void
	{
		DB::table('configs')->where('key', '=', 'title_bucket_mode')->update(['value' => 'alphabetical']);
		DB::table('configs')->where('key', '=', 'title_bucket_prefix_length')->update(['value' => '2']);
		$this->setInstanceDefaults(sorting_col: 'title', granularity: 'year');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->children_of($parent)->owned_by($user)->with_title('Vacation Photos')->create();
		$child->title_base = TitleSplitter::split($child->title)->base;
		$child->save();

		(new RecomputeAlbumStatsJob($child->id, propagate_to_parent: false))->handle();

		$child->refresh();
		$this->assertSame('vacation photos', $child->bucket_key);
	}

	public function testCreatedAtBucketingIsNeverNull(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->children_of($parent)->owned_by($user)->create();

		(new RecomputeAlbumStatsJob($child->id, propagate_to_parent: false))->handle();

		$child->refresh();
		$this->assertNotNull($child->bucket_key);
		$this->assertSame($child->created_at->copy()->utc()->format('Y-m-d'), $child->bucket_key);
	}
}
