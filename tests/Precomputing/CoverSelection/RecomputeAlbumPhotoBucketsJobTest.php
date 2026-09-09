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
use App\Enum\TimelinePhotoGranularity;
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

	public function testAlbumPhotoTimelineChangeRecomputesAllDirectPhotos(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$photo_ids = $this->makeThreePhotos($user, $album);

		// Not yet computed (attached directly via factory, bypassing SetParent).
		foreach ($photo_ids as $id) {
			$row = DB::table('photo_album')->where('photo_id', '=', $id)->where('album_id', '=', $album->id)->first();
			$this->assertNull($row->bucket_id);
		}

		$album->photo_timeline = TimelinePhotoGranularity::MONTH;
		$album->save();

		(new RecomputeAlbumPhotoBucketsJob($album->id))->handle();

		$expected = Photo::find($photo_ids[0])->created_at->format('Y-m');
		foreach ($photo_ids as $id) {
			$row = DB::table('photo_album')->where('photo_id', '=', $id)->where('album_id', '=', $album->id)->first();
			$this->assertSame($expected, $row->bucket_id);
		}
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
			$this->assertNull($row->bucket_id);
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
	 * The job bulk-`upsert()`s every direct photo's `bucket_id`, bypassing
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

		$album->photo_timeline = TimelinePhotoGranularity::MONTH;
		$album->save();

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

		$album->photo_timeline = TimelinePhotoGranularity::MONTH;
		$album->save();

		DB::flushQueryLog();
		DB::enableQueryLog();
		(new RecomputeAlbumPhotoBucketsJob($album->id))->handle();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$write_queries = array_filter($log, fn (array $q) => preg_match('/^(update|insert)/i', trim($q['query'])) === 1);
		$this->assertCount(1, $write_queries, 'Expected exactly one bulk write query, got: ' . implode(' | ', array_column($write_queries, 'query')));
	}

	/**
	 * A photo linked into two albums with different effective photo-sort
	 * settings carries two different `bucket_id` values, one per
	 * `photo_album` row.
	 */
	public function testSamePhotoInTwoAlbumsWithDifferentSettingsDivergesCorrectly(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album_a = Album::factory()->as_root()->owned_by($user)->create();
		$album_b = Album::factory()->as_root()->owned_by($user)->create();
		$album_a->photo_timeline = TimelinePhotoGranularity::YEAR;
		$album_a->save();
		$album_b->photo_timeline = TimelinePhotoGranularity::MONTH;
		$album_b->save();

		$photo = Photo::factory()->owned_by($user)->create();
		DB::table('photo_album')->insert(['photo_id' => $photo->id, 'album_id' => $album_a->id]);
		DB::table('photo_album')->insert(['photo_id' => $photo->id, 'album_id' => $album_b->id]);

		(new RecomputeAlbumPhotoBucketsJob($album_a->id))->handle();
		(new RecomputeAlbumPhotoBucketsJob($album_b->id))->handle();

		$row_a = DB::table('photo_album')->where('photo_id', '=', $photo->id)->where('album_id', '=', $album_a->id)->first();
		$row_b = DB::table('photo_album')->where('photo_id', '=', $photo->id)->where('album_id', '=', $album_b->id)->first();

		$this->assertSame($photo->created_at->format('Y'), $row_a->bucket_id);
		$this->assertSame($photo->created_at->format('Y-m'), $row_b->bucket_id);
		$this->assertNotSame($row_a->bucket_id, $row_b->bucket_id);
	}
}
