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

namespace Tests\ImageProcessing\Jobs;

use App\DTO\UploadStatusEntry;
use App\Enum\UploadStatus;
use App\Exceptions\PhotoRejectedException;
use App\Exceptions\PhotoSkippedException;
use App\Exceptions\QuotaExceededException;
use App\Image\Files\ProcessableJobFile;
use App\Jobs\ProcessImageJob;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Services\UploadStatusStore;
use function Safe\fopen;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequiresFFMpeg;

/**
 * Feature 116: ProcessImageJob records each upload's outcome under its provisional ID
 * (FR-116-01 … 05, FR-116-09, S-116-02 … 11, S-116-14, S-116-15).
 *
 * Asynchronous paths run the job with fake (non-sync) queue interactions, as a worker would.
 */
class UploadStatusJobTest extends BaseApiWithDataTest
{
	use RequiresFFMpeg;

	private const EXPECTED_ID = 'UploadStatusExpectedId01';

	private UploadStatusStore $store;

	public function setUp(): void
	{
		parent::setUp();
		$this->catchFailureSilence = [];
		$this->store = resolve(UploadStatusStore::class);
	}

	public function tearDown(): void
	{
		Configs::set('skip_duplicates', false);
		Configs::set('skip_duplicates_not_owned', true);
		$this->catchFailureSilence = ["App\Exceptions\MediaFileOperationException"];
		parent::tearDown();
	}

	/**
	 * FR-116-01: the job is created in the upload request and records `processing` with the uploader.
	 */
	public function testCreationRecordsProcessing(): void
	{
		$this->actingAs($this->userMayUpload1);

		$this->makeJob($this->album1);

		$entry = $this->entry();
		self::assertSame(UploadStatus::PROCESSING, $entry->status);
		self::assertSame($this->userMayUpload1->id, $entry->uploader_id);
		self::assertNull($entry->photo_id);
	}

	/**
	 * S-116-02: a new photo takes the provisional ID.
	 */
	public function testNewPhotoIsCompleted(): void
	{
		$this->actingAs($this->userMayUpload1);
		$job = $this->makeJob($this->album1);

		$photo = app()->call([$job, 'handle']);

		self::assertInstanceOf(Photo::class, $photo);
		self::assertSame(self::EXPECTED_ID, $photo->id);
		$this->assertEntry(UploadStatus::COMPLETED, self::EXPECTED_ID, null);
		self::assertSame($this->userMayUpload1->id, $this->entry()->uploader_id);
	}

	/**
	 * S-116-03: a duplicate of a photo in another album is linked and reported with the existing ID.
	 */
	public function testDuplicateFromAnotherAlbumIsDuplicate(): void
	{
		$original_id = $this->uploadOriginal($this->album1);
		$job = $this->makeJob($this->subAlbum1);

		app()->call([$job, 'handle']);

		$this->assertEntry(UploadStatus::DUPLICATE, $original_id, null);
		self::assertTrue(Photo::query()->findOrFail($original_id)->albums()->where('id', '=', $this->subAlbum1->id)->exists());
	}

	/**
	 * S-116-04: a duplicate already in the target album no longer disappears.
	 */
	public function testDuplicateInTheSameAlbumIsDuplicate(): void
	{
		$original_id = $this->uploadOriginal($this->album1);
		$job = $this->makeJob($this->album1);

		app()->call([$job, 'handle']);

		$this->assertEntry(UploadStatus::DUPLICATE, $original_id, null);
	}

	/**
	 * S-116-05: with `skip_duplicates` the photo is not added; the entry names the existing photo.
	 */
	public function testSkippedDuplicateIsSkipped(): void
	{
		$original_id = $this->uploadOriginal($this->album1);
		Configs::set('skip_duplicates', true);
		$job = $this->makeJob($this->subAlbum1);

		$result = app()->call([$job, 'handle']);

		self::assertNull($result);
		$job->assertNotFailed();
		$this->assertEntry(UploadStatus::SKIPPED, $original_id, PhotoSkippedException::DEFAULT_MESSAGE);
	}

	/**
	 * S-116-07: a duplicate of a copy waiting for moderation is skipped with its reason.
	 */
	public function testDuplicateOfModerationCopyIsSkipped(): void
	{
		$original_id = $this->uploadOriginal($this->album1);
		Photo::query()->whereKey($original_id)->update(['is_validated' => false]);
		$job = $this->makeJob($this->subAlbum1);

		app()->call([$job, 'handle']);

		$this->assertEntry(UploadStatus::SKIPPED, $original_id, 'The photo has been skipped, there is already a copy waiting for moderation');
	}

	/**
	 * S-116-08 and S-116-10: a duplicate of another user's photo is rejected; between attempts
	 * the entry stays `processing`, after the last one it is `skipped` with the other photo's ID.
	 */
	public function testRejectedDuplicateOfAnotherUserIsSkippedAfterLastAttempt(): void
	{
		Configs::set('skip_duplicates_not_owned', true);
		$original_id = $this->uploadOriginal($this->album2, $this->userMayUpload2->id);
		$this->actingAs($this->userMayUpload1);
		$job = $this->makeJob($this->album1);

		$caught = null;
		try {
			app()->call([$job, 'handle']);
		} catch (PhotoRejectedException $e) {
			$caught = $e;
		}
		self::assertNotNull($caught);
		$this->assertEntry(UploadStatus::PROCESSING, null, null);

		$job->failed($caught);

		$this->assertEntry(UploadStatus::SKIPPED, $original_id, PhotoRejectedException::DEFAULT_MESSAGE);
	}

	/**
	 * S-116-09: a final failure on a Lychee exception keeps its message.
	 */
	public function testFinalFailureOnLycheeExceptionKeepsItsMessage(): void
	{
		$this->actingAs($this->userMayUpload1);
		$job = $this->makeJob($this->album1);

		$job->failed(new QuotaExceededException());

		$this->assertEntry(UploadStatus::FAILED, null, (new QuotaExceededException())->getMessage());
	}

	/**
	 * S-116-09: a final failure on any other throwable hides its message.
	 */
	public function testFinalFailureOnOtherThrowableIsGeneric(): void
	{
		$this->actingAs($this->userMayUpload1);
		$job = $this->makeJob($this->album1);

		$job->failed(new \RuntimeException('SQLSTATE /secret/path'));

		$this->assertEntry(UploadStatus::FAILED, null, UploadStatusEntry::GENERIC_FAILURE);
	}

	/**
	 * S-116-11: a live-photo video joined to its existing still reports the still.
	 */
	public function testVideoJoinedToItsStillIsCompletedWithTheStillId(): void
	{
		$this->assertHasFFMpegOrSkip();
		$this->actingAs($this->userMayUpload1);
		$response = $this->upload('Photo', filename: TestConstants::SAMPLE_FILE_TRAIN_IMAGE, album_id: $this->album1->id);
		$this->assertCreated($response);
		$still_id = $response->json('expected_id');
		self::assertIsString($still_id);

		$job = $this->makeJob($this->album1, TestConstants::SAMPLE_FILE_TRAIN_VIDEO, '.mov', 'train.mov');
		app()->call([$job, 'handle']);

		$this->assertEntry(UploadStatus::COMPLETED, $still_id, null);
	}

	/**
	 * S-116-14: on the sync queue the outcome is stored when the upload answers.
	 */
	public function testSyncUploadStoresTheOutcome(): void
	{
		$this->actingAs($this->userMayUpload1);
		$response = $this->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, album_id: $this->album1->id, file_name: 'night.jpg');
		$this->assertCreated($response);
		$expected_id = $response->json('expected_id');
		self::assertIsString($expected_id);

		$entry = $this->store->many([$expected_id])[0];
		self::assertNotNull($entry);
		self::assertSame(UploadStatus::COMPLETED, $entry->status);
		self::assertSame($expected_id, $entry->photo_id);
		self::assertSame($this->userMayUpload1->id, $entry->uploader_id);
	}

	/**
	 * S-116-15: a job without a provisional ID (ZIP files, imports) records nothing.
	 */
	public function testJobWithoutProvisionalIdRecordsNothing(): void
	{
		$spy = $this->spy(UploadStatusStore::class);
		$this->actingAs($this->userMayUpload1);
		$job = new ProcessImageJob($this->jobFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE, '.jpg', 'night.jpg'), $this->album1, null);
		$job->withFakeQueueInteractions();

		app()->call([$job, 'handle']);
		$job->failed(new \RuntimeException('boom'));

		$spy->shouldNotHaveReceived('record');
	}

	/**
	 * Upload the night photo through the API (sync queue) and return its ID.
	 */
	private function uploadOriginal(?Album $album, ?int $owner_id = null): string
	{
		$owner = $owner_id === $this->userMayUpload2->id ? $this->userMayUpload2 : $this->userMayUpload1;
		$response = $this->actingAs($owner)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, album_id: $album?->id, file_name: 'night.jpg');
		$this->assertCreated($response);
		$id = $response->json('expected_id');
		self::assertIsString($id);
		$this->actingAs($this->userMayUpload1);

		return $id;
	}

	private function makeJob(
		Album $album,
		string $sample = TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
		string $extension = '.jpg',
		string $name = 'night.jpg',
	): ProcessImageJob {
		$job = new ProcessImageJob($this->jobFile($sample, $extension, $name), $album, null, null, self::EXPECTED_ID);
		$job->withFakeQueueInteractions();

		return $job;
	}

	private function jobFile(string $sample, string $extension, string $name): ProcessableJobFile
	{
		$file = new ProcessableJobFile($extension, $name);
		$file->write(fopen(base_path($sample), 'rb'));
		$file->close();

		return $file;
	}

	private function entry(): UploadStatusEntry
	{
		$entry = $this->store->many([self::EXPECTED_ID])[0];
		self::assertNotNull($entry, 'No upload status recorded for ' . self::EXPECTED_ID);

		return $entry;
	}

	private function assertEntry(UploadStatus $status, ?string $photo_id, ?string $message): void
	{
		$entry = $this->entry();
		self::assertSame($status, $entry->status);
		self::assertSame($photo_id, $entry->photo_id);
		self::assertSame($message, $entry->message);
	}
}
