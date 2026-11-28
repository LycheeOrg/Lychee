<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Actions\Photo;

use App\Actions\Photo\PurgeTrash;
use App\Actions\Photo\PurgeTrashResult;
use App\Actions\Photo\Trash;
use App\Jobs\FileDeleterJob;
use App\Jobs\PurgeTrashJob;
use App\Models\Album;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Tests\AbstractTestCase;
use Tests\Traits\CreatesBulkFixtures;

/**
 * Feature 119 (FR-119-04/05, NFR-119-03, S-119-07..09, S-119-13): the purge
 * deletes trashed photos in batches, each in its own transaction.
 */
class PurgeTrashServiceTest extends AbstractTestCase
{
	use DatabaseTransactions;
	use CreatesBulkFixtures;

	private User $owner;
	private Album $album;

	public function setUp(): void
	{
		parent::setUp();
		Bus::fake();
		$this->owner = User::factory()->create();
		$this->album = Album::factory()->as_root()->owned_by($this->owner)->create();
	}

	public function testPurgeDeletesTrashedPhotosAndDispatchesTheirFileJobs(): void
	{
		$photo = $this->trashedPhoto();

		$result = (new PurgeTrash())->run();

		self::assertSame(1, $result->purged);
		self::assertSame(0, $result->left);
		$this->assertDatabaseMissing('photos', ['id' => $photo->id]);
		$this->assertDatabaseMissing('trashed_photos', ['photo_id' => $photo->id]);
		$this->assertDatabaseMissing('size_variants', ['photo_id' => $photo->id]);
		Bus::assertDispatched(FileDeleterJob::class);
	}

	/**
	 * S-119-07: a trashed photo that has an album link again leaves the
	 * trash and is not deleted.
	 */
	public function testARelinkedPhotoLeavesTheTrash(): void
	{
		$photo = $this->trashedPhoto();
		DB::table('photo_album')->insert(['photo_id' => $photo->id, 'album_id' => $this->album->id]);

		$result = (new PurgeTrash())->run();

		self::assertSame(0, $result->purged);
		self::assertSame(1, $result->restored);
		$this->assertDatabaseHas('photos', ['id' => $photo->id]);
		$this->assertDatabaseMissing('trashed_photos', ['photo_id' => $photo->id]);
	}

	/**
	 * S-119-08: the second batch throws: the first one is purged, the
	 * second one's photos are still in the trash, the error is reported.
	 */
	public function testAFailingBatchLeavesItsPhotosInTheTrash(): void
	{
		Exceptions::fake();
		$ids = $this->trashedBulkPhotos(PurgeTrash::BATCH_SIZE + 1);
		sort($ids);
		$second_batch = array_slice($ids, PurgeTrash::BATCH_SIZE);
		DB::listen(function (QueryExecuted $query) use ($second_batch): void {
			if (str_starts_with(strtolower($query->sql), 'delete from "photos"') && array_intersect($query->bindings, $second_batch) !== []) {
				throw new \RuntimeException('second batch fails');
			}
		});

		$result = (new PurgeTrash())->run();

		self::assertSame(PurgeTrash::BATCH_SIZE, $result->purged);
		self::assertSame(1, $result->left);
		self::assertFalse($result->budget_exhausted);
		$this->assertDatabaseHas('trashed_photos', ['photo_id' => $second_batch[0]]);
		$this->assertDatabaseHas('photos', ['id' => $second_batch[0]]);
		Exceptions::assertReported(fn (\RuntimeException $e) => $e->getMessage() === 'second batch fails');
	}

	/**
	 * S-119-09, NFR-119-03: with a time budget of 0 s one batch is purged;
	 * without a budget the trash is emptied.
	 */
	public function testTheTimeBudgetStopsAfterOneBatch(): void
	{
		$this->trashedBulkPhotos(PurgeTrash::BATCH_SIZE + 1);

		$result = (new PurgeTrash())->run(null, 0.0);
		self::assertSame(PurgeTrash::BATCH_SIZE, $result->purged);
		self::assertSame(1, $result->left);
		self::assertTrue($result->budget_exhausted);

		$result = (new PurgeTrash())->run();
		self::assertSame(1, $result->purged);
		self::assertSame(0, $result->left);
	}

	public function testTheLimitBoundsThePhotosPurged(): void
	{
		$this->trashedBulkPhotos(3);

		$result = (new PurgeTrash())->run(2);

		self::assertSame(2, $result->purged);
		self::assertSame(1, $result->left);
	}

	public function testTheOldestTrashedPhotosArePurgedFirst(): void
	{
		$newer = $this->trashedPhoto();
		$older = $this->trashedPhoto();
		DB::table('trashed_photos')->where('photo_id', '=', $older->id)->update(['trashed_at' => now()->subDay()]);

		(new PurgeTrash())->run(1);

		$this->assertDatabaseMissing('photos', ['id' => $older->id]);
		$this->assertDatabaseHas('photos', ['id' => $newer->id]);
	}

	/**
	 * S-119-13: a sold size variant keeps its row and file, detached.
	 */
	public function testASoldVariantIsKeptDetached(): void
	{
		$photo = Photo::factory()->owned_by($this->owner)->in($this->album)->create();
		$sold = SizeVariant::query()->where('photo_id', '=', $photo->id)->firstOrFail();
		$order = Order::factory()->create(['user_id' => $this->owner->id]);
		OrderItem::factory()->create(['order_id' => $order->id, 'photo_id' => $photo->id, 'size_variant_id' => $sold->id, 'title' => 'sold']);
		(new Trash())->everywhere([$photo->id]);

		(new PurgeTrash())->run();

		$this->assertDatabaseHas('size_variants', ['id' => $sold->id, 'photo_id' => null]);
		$this->assertDatabaseMissing('photos', ['id' => $photo->id]);
	}

	/**
	 * FR-119-05: 10 s on `sync` in an HTTP request, none in console on
	 * `sync`, 30 minutes with a worker.
	 */
	public function testTheJobBudgetDependsOnWhereItRuns(): void
	{
		self::assertSame((float) PurgeTrash::SYNC_TIME_BUDGET, PurgeTrashJob::budget(sync: true, console: false));
		self::assertNull(PurgeTrashJob::budget(sync: true, console: true));
		self::assertSame((float) PurgeTrash::ASYNC_TIME_BUDGET, PurgeTrashJob::budget(sync: false, console: true));
		self::assertSame((float) PurgeTrash::ASYNC_TIME_BUDGET, PurgeTrashJob::budget(sync: false, console: false));
	}

	/**
	 * S-119-09: with a worker, a job whose budget ran out with photos left
	 * dispatches itself again; a failed batch does not loop.
	 */
	public function testTheJobContinuesOnlyWhenItsBudgetRanOutWithAWorker(): void
	{
		self::assertTrue(PurgeTrashJob::shouldContinue(new PurgeTrashResult(100, 0, 5, true), sync: false));
		self::assertFalse(PurgeTrashJob::shouldContinue(new PurgeTrashResult(100, 0, 5, true), sync: true));
		self::assertFalse(PurgeTrashJob::shouldContinue(new PurgeTrashResult(100, 0, 5, false), sync: false));
		self::assertFalse(PurgeTrashJob::shouldContinue(new PurgeTrashResult(100, 0, 0, true), sync: false));
	}

	private function trashedPhoto(): Photo
	{
		$photo = Photo::factory()->owned_by($this->owner)->in($this->album)->create();
		(new Trash())->everywhere([$photo->id]);

		return $photo;
	}

	/**
	 * @return string[]
	 */
	private function trashedBulkPhotos(int $count): array
	{
		$ids = $this->bulkPhotos($this->owner, $count, [$this->album->id]);
		(new Trash())->everywhere($ids);

		return $ids;
	}
}
