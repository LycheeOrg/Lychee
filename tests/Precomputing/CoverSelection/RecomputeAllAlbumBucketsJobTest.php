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
use App\Events\AlbumListingCacheFlushRequested;
use App\Jobs\RecomputeAllAlbumBucketsJob;
use App\Models\Album;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test RecomputeAllAlbumBucketsJob (FR-092-13, S-092-10): every album key
 * is rewritten, root albums and the children of parents without their own
 * album sorting included; children of a parent with its own sorting keep
 * the parent's column.
 */
class RecomputeAllAlbumBucketsJobTest extends BasePrecomputingTest
{
	public function testRewritesEveryAlbumForTheInstanceDefault(): void
	{
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update(['value' => 'title']);
		DB::table('configs')->where('key', '=', 'title_bucket_mode')->update(['value' => 'date_prefix']);
		$user = User::factory()->create();
		$root = Album::factory()->as_root()->owned_by($user)->with_title('2021 Root')->create();
		$child = Album::factory()->children_of($root)->owned_by($user)->with_title('2022-07 Child')->create();
		$override_parent = Album::factory()->as_root()->owned_by($user)->with_title('2023 Parent')->create();
		$override_parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::OWNER_ID, OrderSortingType::ASC);
		$override_parent->save();
		$override_child = Album::factory()->children_of($override_parent)->owned_by($user)->with_title('2024 Child')->create();
		DB::table('albums')->update(['bucket_key' => 'stale']);

		Event::fake([AlbumListingCacheFlushRequested::class]);
		(new RecomputeAllAlbumBucketsJob())->handle();

		$this->assertSame('2021-01-01', $root->fresh()->bucket_key);
		$this->assertSame('2022-07-01', $child->fresh()->bucket_key);
		$this->assertSame('2023-01-01', $override_parent->fresh()->bucket_key);
		// The parent's own OWNER_ID sorting is not bucketable.
		$this->assertNull($override_child->fresh()->bucket_key);
		Event::assertDispatched(AlbumListingCacheFlushRequested::class);
	}
}
