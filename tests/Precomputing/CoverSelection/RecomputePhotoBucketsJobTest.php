<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\CoverSelection;

use App\Enum\TimelinePhotoGranularity;
use App\Jobs\RecomputePhotoBucketsJob;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test RecomputePhotoBucketsJob.
 */
class RecomputePhotoBucketsJobTest extends BasePrecomputingTest
{
	private function setInstanceDefaults(string $sorting_col = 'created_at', string $granularity = 'year'): void
	{
		DB::table('configs')->where('key', '=', 'sorting_photos_col')->update(['value' => $sorting_col]);
		DB::table('configs')->where('key', '=', 'timeline_photos_granularity')->update(['value' => $granularity]);
	}

	public function testPhotoMetadataChangeRecomputesLinkedAlbum(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$album->photo_timeline = TimelinePhotoGranularity::MONTH;
		$album->save();

		$photo = Photo::factory()->owned_by($user)->in($album)->create();

		$row = DB::table('photo_album')->where('photo_id', '=', $photo->id)->first();
		$this->assertNull($row->bucket_id);

		(new RecomputePhotoBucketsJob($photo->id))->handle();

		$row = DB::table('photo_album')->where('photo_id', '=', $photo->id)->first();
		$this->assertSame($photo->created_at->format('Y-m'), $row->bucket_id);
	}

	public function testUnknownPhotoOrZeroLinksIsNoOp(): void
	{
		// Must not throw.
		(new RecomputePhotoBucketsJob('nonexistent-photo-id-0000000'))->handle();
		$this->assertTrue(true);
	}

	public function testRecomputesInOneBulkUpdateNotOnePerAlbum(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album1 = Album::factory()->as_root()->owned_by($user)->create();
		$album2 = Album::factory()->as_root()->owned_by($user)->create();

		$photo = Photo::factory()->owned_by($user)->create();
		DB::table('photo_album')->insert(['photo_id' => $photo->id, 'album_id' => $album1->id]);
		DB::table('photo_album')->insert(['photo_id' => $photo->id, 'album_id' => $album2->id]);

		DB::flushQueryLog();
		DB::enableQueryLog();
		(new RecomputePhotoBucketsJob($photo->id))->handle();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$write_queries = array_filter($log, fn (array $q) => preg_match('/^(update|insert)/i', trim($q['query'])) === 1);
		$this->assertCount(1, $write_queries, 'Expected exactly one bulk write query, got: ' . implode(' | ', array_column($write_queries, 'query')));
	}

	/**
	 * From the other trigger direction: recomputing one photo that is
	 * linked into two albums with different effective settings yields two
	 * different `bucket_id` values, one per `photo_album` row.
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

		(new RecomputePhotoBucketsJob($photo->id))->handle();

		$row_a = DB::table('photo_album')->where('photo_id', '=', $photo->id)->where('album_id', '=', $album_a->id)->first();
		$row_b = DB::table('photo_album')->where('photo_id', '=', $photo->id)->where('album_id', '=', $album_b->id)->first();

		$this->assertSame($photo->created_at->format('Y'), $row_a->bucket_id);
		$this->assertSame($photo->created_at->format('Y-m'), $row_b->bucket_id);
		$this->assertNotSame($row_a->bucket_id, $row_b->bucket_id);
	}

	public function testAlbumWithOwnPerAlbumSortOverrideIsUsedOverInstanceDefault(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$album->photo_sorting = new \App\DTO\PhotoSortingCriterion(\App\Enum\ColumnSortingType::OWNER_ID, \App\Enum\OrderSortingType::ASC);
		$album->save();

		$photo = Photo::factory()->owned_by($user)->create();
		DB::table('photo_album')->insert(['photo_id' => $photo->id, 'album_id' => $album->id]);

		(new RecomputePhotoBucketsJob($photo->id))->handle();

		$row = DB::table('photo_album')->where('photo_id', '=', $photo->id)->where('album_id', '=', $album->id)->first();
		$this->assertNull($row->bucket_id);
	}
}
