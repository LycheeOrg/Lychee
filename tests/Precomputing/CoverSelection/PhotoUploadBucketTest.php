<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\CoverSelection;

use App\Actions\Photo\MoveOrDuplicate;
use App\Actions\Shop\PurchasableService;
use App\Enum\TimelinePhotoGranularity;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Covers a new `photo_album` pivot-link insert
 * (here via {@see MoveOrDuplicate::do()}'s move/copy insert branch) computes
 * `bucket_id` inline against the destination album's own currently
 * effective settings — no separate recompute job needed for this trigger.
 */
class PhotoUploadBucketTest extends BasePrecomputingTest
{
	private function setInstanceDefaults(string $sorting_col = 'created_at', string $granularity = 'year'): void
	{
		DB::table('configs')->where('key', '=', 'sorting_photos_col')->update(['value' => $sorting_col]);
		DB::table('configs')->where('key', '=', 'timeline_photos_granularity')->update(['value' => $granularity]);
	}

	public function testMoveIntoAlbumComputesBucketIdInline(): void
	{
		$this->setInstanceDefaults(sorting_col: 'created_at', granularity: 'year');
		$user = User::factory()->create();
		$dest = Album::factory()->as_root()->owned_by($user)->create();
		$dest->photo_timeline = TimelinePhotoGranularity::MONTH;
		$dest->save();

		$photo = Photo::factory()->owned_by($user)->create();

		$move_action = new MoveOrDuplicate(resolve(PurchasableService::class));
		$move_action->do(collect([$photo]), null, $dest);

		$row = DB::table('photo_album')->where('photo_id', '=', $photo->id)->where('album_id', '=', $dest->id)->first();
		$this->assertNotNull($row);
		$this->assertSame($photo->created_at->format('Y-m'), $row->bucket_id);
	}

	/**
	 * Copying (not moving) a photo into a second album with different
	 * `sorting_col`/`photo_timeline` settings gives each `photo_album` row
	 * its own, independently correct `bucket_id`.
	 */
	public function testCopyIntoSecondAlbumWithDifferentSettingsDivergesCorrectly(): void
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

		$move_action = new MoveOrDuplicate(resolve(PurchasableService::class));
		// Insert into album A first (move from root).
		$move_action->do(collect([$photo]), null, $album_a);
		// Copy (from === to === album_b is not the semantics here; the
		// "copy" case is $from_album === $to_album for CopyPhotosRequest,
		// but the structural point that matters here is simply: the
		// same photo linked into two different albums with different
		// settings gets two different bucket_id values). Insert the second
		// link directly, mirroring what CopyPhotosRequest's controller call
		// does under the hood ($duplicate->do($photos, $album, $album)
		// keeps the existing link and adds nothing new) - here we exercise
		// the actual multi-album-link scenario via a second move-style
		// insert that does not delete album_a's own link.
		$move_action->do(collect([$photo]), null, $album_b);

		$row_a = DB::table('photo_album')->where('photo_id', '=', $photo->id)->where('album_id', '=', $album_a->id)->first();
		$row_b = DB::table('photo_album')->where('photo_id', '=', $photo->id)->where('album_id', '=', $album_b->id)->first();

		$this->assertNotNull($row_a);
		$this->assertNotNull($row_b);
		$this->assertSame($photo->created_at->format('Y'), $row_a->bucket_id);
		$this->assertSame($photo->created_at->format('Y-m'), $row_b->bucket_id);
		$this->assertNotSame($row_a->bucket_id, $row_b->bucket_id);
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
