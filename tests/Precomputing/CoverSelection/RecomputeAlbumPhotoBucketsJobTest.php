<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\CoverSelection;

use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Events\AlbumPhotoSortingChanged;
use App\Jobs\RecomputeAlbumPhotoBucketsJob;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test RecomputeAlbumPhotoBucketsJob.
 */
class RecomputeAlbumPhotoBucketsJobTest extends BasePrecomputingTest
{
	private function setInstanceDefaults(string $sorting_col = 'created_at', string $granularity = 'year'): void
	{
		DB::table('configs')->where('key', '=', 'sorting_photos_col')->update(['value' => $sorting_col]);
		DB::table('configs')->where('key', '=', 'timeline_photos_granularity')->update(['value' => $granularity]);
	}

	/** @return string[] */
	private function makeThreePhotos(User $user, Album $album): array
	{
		$ids = [];
		foreach (range(1, 3) as $_) {
			$photo = Photo::factory()->owned_by($user)->in($album)->create();
			$ids[] = $photo->id;
		}

		return $ids;
	}

	public function testAlbumSortColumnChangeRecomputesAllDirectPhotos(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$photo_ids = $this->makeThreePhotos($user, $album);

		// Not yet computed (attached directly via factory, bypassing SetParent).
		foreach ($photo_ids as $id) {
			$row = DB::table('photo_album')->where('photo_id', '=', $id)->where('album_id', '=', $album->id)->first();
			$this->assertNull($row->bucket_key);
		}

		$album->photo_sorting = new PhotoSortingCriterion(ColumnSortingType::TYPE, OrderSortingType::ASC);
		$album->save();

		(new RecomputeAlbumPhotoBucketsJob($album->id))->handle();

		foreach ($photo_ids as $id) {
			$row = DB::table('photo_album')->where('photo_id', '=', $id)->where('album_id', '=', $album->id)->first();
			$this->assertSame('image/jpeg', $row->bucket_key);
		}
	}

	/**
	 * FR-092-14, S-092-12: 1,001 links are written in two statements, every
	 * key correct.
	 */
	public function testRecomputesInChunksOfAThousand(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$template = (array) DB::table('photos')->where('id', '=', Photo::factory()->owned_by($user)->in($album)->create()->id)->first();
		$rows = [];
		$links = [];
		for ($i = 0; $i < 1000; $i++) {
			$id = sprintf('chunk%019d', $i);
			$rows[] = array_merge($template, ['id' => $id, 'created_at' => sprintf('2020-01-%02d %02d:00:00.000000', 1 + $i % 28, $i % 24)]);
			$links[] = ['photo_id' => $id, 'album_id' => $album->id];
		}
		foreach (array_chunk($rows, 100) as $chunk) {
			DB::table('photos')->insert($chunk);
		}
		foreach (array_chunk($links, 500) as $chunk) {
			DB::table('photo_album')->insert($chunk);
		}

		DB::flushQueryLog();
		DB::enableQueryLog();
		(new RecomputeAlbumPhotoBucketsJob($album->id))->handle();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$write_queries = array_filter($log, fn (array $q) => preg_match('/^(update|insert)/i', trim($q['query'])) === 1);
		$this->assertCount(2, $write_queries);
		$this->assertSame(1001, DB::table('photo_album')->where('album_id', '=', $album->id)->whereNotNull('bucket_key')->count());
		$this->assertSame('2020-01-05-04', DB::table('photo_album')->where('photo_id', '=', sprintf('chunk%019d', 4))->value('bucket_key'));
	}

	public function testAlbumSortingColChangeToOwnerIdRecomputesAllToNull(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$photo_ids = $this->makeThreePhotos($user, $album);

		$album->photo_sorting = new PhotoSortingCriterion(ColumnSortingType::OWNER_ID, OrderSortingType::ASC);
		$album->save();

		(new RecomputeAlbumPhotoBucketsJob($album->id))->handle();

		foreach ($photo_ids as $id) {
			$row = DB::table('photo_album')->where('photo_id', '=', $id)->where('album_id', '=', $album->id)->first();
			$this->assertNull($row->bucket_key);
		}
	}

	public function testZeroPhotosAlbumIsNoOp(): void
	{
		$this->setInstanceDefaults();
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		Event::fake([AlbumPhotoSortingChanged::class]);

		// Must not throw.
		(new RecomputeAlbumPhotoBucketsJob($album->id))->handle();
		$this->assertTrue(true);

		// No rows were touched, so there is nothing to invalidate.
		Event::assertNotDispatched(AlbumPhotoSortingChanged::class);
	}

	public function testUnknownAlbumIsNoOp(): void
	{
		Event::fake([AlbumPhotoSortingChanged::class]);

		// Must not throw.
		(new RecomputeAlbumPhotoBucketsJob('nonexistent-album-id-000000'))->handle();
		$this->assertTrue(true);

		Event::assertNotDispatched(AlbumPhotoSortingChanged::class);
	}

	/**
	 * The job bulk-`upsert()`s every direct photo's `bucket_key`, bypassing
	 * Eloquent events entirely - it must dispatch the dedicated
	 * photo-listing cache-invalidation signal itself once that write has
	 * landed, since nothing else will (regression coverage for the bucket
	 * recompute + cache invalidation gap).
	 */
	public function testDispatchesAlbumPhotoSortingChangedAfterUpsert(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$this->makeThreePhotos($user, $album);

		Event::fake([AlbumPhotoSortingChanged::class]);

		(new RecomputeAlbumPhotoBucketsJob($album->id))->handle();

		Event::assertDispatched(AlbumPhotoSortingChanged::class, fn (AlbumPhotoSortingChanged $event) => $event->album_ids === [$album->id]);
	}

	public function testRecomputesInOneBulkUpdateNotOnePerPhoto(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$this->makeThreePhotos($user, $album);

		DB::flushQueryLog();
		DB::enableQueryLog();
		(new RecomputeAlbumPhotoBucketsJob($album->id))->handle();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$write_queries = array_filter($log, fn (array $q) => preg_match('/^(update|insert)/i', trim($q['query'])) === 1);
		$this->assertCount(1, $write_queries, 'Expected exactly one bulk write query, got: ' . implode(' | ', array_column($write_queries, 'query')));
	}
}
