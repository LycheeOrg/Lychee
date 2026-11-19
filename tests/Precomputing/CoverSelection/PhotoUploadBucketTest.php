<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\CoverSelection;

use App\Actions\Photo\MoveOrDuplicate;
use App\Actions\Shop\PurchasableService;
use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Enum\TimelinePhotoGranularity;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Covers a new `photo_album` pivot-link insert
 * (here via {@see MoveOrDuplicate::do()}'s move/copy insert branch) writes
 * the granularity-free `bucket_key` inline for the destination album's
 * effective sort column (FR-092-03) — no separate recompute job needed.
 */
class PhotoUploadBucketTest extends BasePrecomputingTest
{
	private function setInstanceDefaults(string $sorting_col = 'created_at', string $granularity = 'year'): void
	{
		DB::table('configs')->where('key', '=', 'sorting_photos_col')->update(['value' => $sorting_col]);
		DB::table('configs')->where('key', '=', 'timeline_photos_granularity')->update(['value' => $granularity]);
	}

	public function testMoveIntoAlbumWritesGranularityFreeKey(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$dest = Album::factory()->as_root()->owned_by($user)->create();
		$dest->photo_timeline = TimelinePhotoGranularity::MONTH;
		$dest->save();

		$photo = Photo::factory()->owned_by($user)->create(['created_at' => '2026-07-01 14:05:00']);

		$move_action = new MoveOrDuplicate(resolve(PurchasableService::class));
		$move_action->do(collect([$photo]), null, $dest);

		$row = DB::table('photo_album')->where('photo_id', '=', $photo->id)->where('album_id', '=', $dest->id)->first();
		$this->assertNotNull($row);
		// Neither the instance (year) nor the album (month) granularity
		// shapes the key: it is `created_at` as stored, to the hour.
		$this->assertSame('2026-07-01-14', $row->bucket_key);
	}

	/**
	 * Linking a photo into two albums with different sort columns gives each
	 * `photo_album` row its own key.
	 */
	public function testLinksIntoAlbumsWithDifferentSortColumnsDiverge(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album_a = Album::factory()->as_root()->owned_by($user)->create();
		$album_b = Album::factory()->as_root()->owned_by($user)->create();
		$album_b->photo_sorting = new PhotoSortingCriterion(ColumnSortingType::TITLE, OrderSortingType::ASC);
		$album_b->save();

		$photo = Photo::factory()->owned_by($user)->with_title('2024-03 Trip')->create(['created_at' => '2026-07-01 14:05:00']);

		$move_action = new MoveOrDuplicate(resolve(PurchasableService::class));
		// Two move-style inserts from root keep both links.
		$move_action->do(collect([$photo]), null, $album_a);
		$move_action->do(collect([$photo]), null, $album_b);

		$row_a = DB::table('photo_album')->where('photo_id', '=', $photo->id)->where('album_id', '=', $album_a->id)->first();
		$row_b = DB::table('photo_album')->where('photo_id', '=', $photo->id)->where('album_id', '=', $album_b->id)->first();

		$this->assertNotNull($row_a);
		$this->assertNotNull($row_b);
		$this->assertSame('2026-07-01-14', $row_a->bucket_key);
		$this->assertSame('2024-03-01', $row_b->bucket_key);
	}

	public function testBulkMoveInsertsInOneQueryNotOnePerPhoto(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$dest = Album::factory()->as_root()->owned_by($user)->create();
		$photos = collect([
			Photo::factory()->owned_by($user)->create(),
			Photo::factory()->owned_by($user)->create(),
			Photo::factory()->owned_by($user)->create(),
		]);

		$move_action = new MoveOrDuplicate(resolve(PurchasableService::class));

		DB::flushQueryLog();
		DB::enableQueryLog();
		$move_action->do($photos, null, $dest);
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$insert_queries = array_filter($log, fn (array $q) => preg_match('/^insert into [`"]?photo_album[`"]?/i', trim($q['query'])) === 1);
		$this->assertCount(1, $insert_queries, 'Expected exactly one bulk insert query, got: ' . implode(' | ', array_column($insert_queries, 'query')));
	}
}
