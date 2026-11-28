<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2;

use App\Actions\Photo\PurgeTrash;
use App\Jobs\DeleteFaceEmbeddingsJob;
use App\Jobs\FileDeleterJob;
use App\Jobs\PurgeTrashJob;
use App\Jobs\RecomputePersonStatsJob;
use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use Illuminate\Support\Facades\Bus;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * FR-112-04: on the `sync` queue, the jobs a delete action dispatches after
 * its DB commit run after the HTTP response. Other connections queue them.
 *
 * Scenarios covered:
 * - S-112-03: album delete on `sync`, jobs after response, DB rows already gone.
 * - S-112-04: album delete on an async queue, jobs queued normally.
 * - S-112-18: photo delete on `sync` and async (thumb part in PhotoDeleteFanOutTest).
 *
 * Feature 119: a delete trashes the photos and dispatches the purge, after the
 * response on `sync`, queued otherwise. The purge dispatches the file,
 * person-stats and face-embedding jobs of the photos it deletes.
 */
class DeleteAfterResponseTest extends BaseApiWithDataTest
{
	/** @var class-string[] */
	private const DEFERRED_JOBS = [
		FileDeleterJob::class,
		RecomputePersonStatsJob::class,
		DeleteFaceEmbeddingsJob::class,
	];

	public function testAlbumDeleteOnSyncDispatchesJobsAfterResponse(): void
	{
		Bus::fake();
		$this->addFaceTo($this->subPhoto1);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Album', [
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertNoContent($response);

		$this->assertDatabaseMissing('albums', ['id' => $this->subAlbum1->id]);
		self::assertFalse(Photo::query()->whereKey($this->subPhoto1->id)->exists());
		$this->assertAllAfterResponse();
	}

	public function testAlbumDeleteOnAsyncQueueQueuesJobs(): void
	{
		config(['queue.default' => 'database']);
		Bus::fake();
		$this->addFaceTo($this->subPhoto1);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Album', [
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertNoContent($response);

		$this->assertAllQueued();
	}

	public function testPhotoDeleteOnSyncDispatchesJobsAfterResponse(): void
	{
		Bus::fake();
		$this->addFaceTo($this->subPhoto1);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', [
			'photo_ids' => [$this->subPhoto1->id],
			'from_id' => $this->subAlbum1->id,
		]);
		$this->assertNoContent($response);

		self::assertFalse(Photo::query()->whereKey($this->subPhoto1->id)->exists());
		$this->assertAllAfterResponse();
	}

	public function testPhotoDeleteOnAsyncQueueQueuesJobs(): void
	{
		config(['queue.default' => 'database']);
		Bus::fake();
		$this->addFaceTo($this->subPhoto1);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', [
			'photo_ids' => [$this->subPhoto1->id],
			'from_id' => $this->subAlbum1->id,
		]);
		$this->assertNoContent($response);

		$this->assertAllQueued();
	}

	private function addFaceTo(Photo $photo): void
	{
		$person = Person::factory()->create();
		Face::factory()->for_photo($photo)->for_person($person)->create();
	}

	private function assertAllAfterResponse(): void
	{
		Bus::assertDispatchedAfterResponse(PurgeTrashJob::class);
		self::assertCount(0, Bus::dispatched(PurgeTrashJob::class), 'PurgeTrashJob dispatched inline.');
		$this->assertDeferredJobsComeFromThePurge();
	}

	private function assertAllQueued(): void
	{
		self::assertNotCount(0, Bus::dispatched(PurgeTrashJob::class), 'PurgeTrashJob not queued.');
		Bus::assertNotDispatchedAfterResponse(PurgeTrashJob::class);
		$this->assertDeferredJobsComeFromThePurge();
	}

	/**
	 * None of the file, person-stats and face-embedding jobs is dispatched by
	 * the delete itself; the purge dispatches each of them.
	 */
	private function assertDeferredJobsComeFromThePurge(): void
	{
		foreach (self::DEFERRED_JOBS as $job_class) {
			self::assertCount(0, Bus::dispatched($job_class), $job_class . ' dispatched by the delete.');
			Bus::assertNotDispatchedAfterResponse($job_class);
		}

		(new PurgeTrash())->run();

		foreach (self::DEFERRED_JOBS as $job_class) {
			self::assertNotCount(0, Bus::dispatched($job_class), $job_class . ' not dispatched by the purge.');
		}
		$this->assertDatabaseMissing('photos', ['id' => $this->subPhoto1->id]);
	}
}
