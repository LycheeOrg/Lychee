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

use App\Constants\PhotoAlbum as PA;
use App\DTO\ImportMode;
use App\Enum\JobStatus;
use App\Enum\UserUploadTrustLevel;
use App\Exceptions\PhotoRejectedException;
use App\Exceptions\PhotoSkippedException;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\ProcessableJobFile;
use App\Jobs\ImportImageJob;
use App\Jobs\ProcessImageJob;
use App\Models\Configs;
use App\Models\JobHistory;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use function Safe\fopen;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 114: a photo skipped as a duplicate is a handled outcome, not a failure.
 *
 * Async path: the job runs with a fake (non-sync) queue job; it must end normally
 * (no exception, not failed, not released) and record JobStatus::SKIPPED.
 * Sync path: the skip still reaches the caller (409 / exception) and records SKIPPED.
 */
class SkippedDuplicateJobTest extends BaseApiWithDataTest
{
	/** @var MessageLogged[] */
	private array $logged = [];

	public function setUp(): void
	{
		parent::setUp();
		$this->catchFailureSilence = [];
		$this->logged = [];
		Event::listen(MessageLogged::class, function (MessageLogged $event): void {
			$this->logged[] = $event;
		});

		// The original, so that the next import of the same file is a duplicate.
		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, file_name: 'night.jpg');
		$this->assertCreated($response);
		Configs::set('skip_duplicates', true);
	}

	public function tearDown(): void
	{
		Configs::set('skip_duplicates', false);
		Configs::set('skip_duplicates_not_owned', true);
		$this->catchFailureSilence = ["App\Exceptions\MediaFileOperationException"];
		parent::tearDown();
	}

	/**
	 * S-114-01: async import of a duplicate ends normally and records SKIPPED.
	 */
	public function testAsyncImportSkipCompletesWithoutFailure(): void
	{
		$job = $this->makeImportJob(new ImportMode(skip_duplicates: true));
		$job->withFakeQueueInteractions();

		$result = app()->call([$job, 'handle']);

		self::assertNull($result);
		$job->assertNotFailed();
		$job->assertNotReleased();
		$this->assertLatestHistoryStatus(JobStatus::SKIPPED);
		$this->assertSkipLogged('night.jpg', 'The photo has been skipped');
		$this->assertNoErrorLogged();
	}

	/**
	 * S-114-02: async import of a duplicate whose metadata is resynced ends normally and records SKIPPED.
	 */
	public function testAsyncImportResyncCompletesWithoutFailure(): void
	{
		// Clear one metadata field of the original so that HydrateMetadata makes the duplicate dirty.
		$original = $this->originalPhoto();
		$original->iso = null;
		$original->save();

		$job = $this->makeImportJob(new ImportMode(skip_duplicates: true, resync_metadata: true));
		$job->withFakeQueueInteractions();

		$result = app()->call([$job, 'handle']);

		self::assertNull($result);
		$job->assertNotFailed();
		$job->assertNotReleased();
		$this->assertLatestHistoryStatus(JobStatus::SKIPPED);
		$this->assertSkipLogged('night.jpg', 'metadata has been updated');
		$this->assertNoErrorLogged();
		self::assertSame('1250', $this->originalPhoto()->iso);
	}

	/**
	 * S-114-03: async web upload of a duplicate ends normally and records SKIPPED.
	 */
	public function testAsyncUploadSkipCompletesWithoutFailure(): void
	{
		$this->actingAs($this->admin);
		$file = new ProcessableJobFile('.jpg', 'night.jpg');
		$file->write(fopen(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE), 'rb'));
		$file->close();

		$job = new ProcessImageJob($file, null, null);
		$job->withFakeQueueInteractions();

		$result = app()->call([$job, 'handle']);

		self::assertNull($result);
		$job->assertNotFailed();
		$job->assertNotReleased();
		$this->assertLatestHistoryStatus(JobStatus::SKIPPED);
		$this->assertSkipLogged('night.jpg', 'The photo has been skipped');
		$this->assertNoErrorLogged();
	}

	/**
	 * S-114-04: sync upload of a duplicate still answers 409 and records SKIPPED.
	 */
	public function testSyncUploadSkipStillConflicts(): void
	{
		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, file_name: 'night.jpg');
		$this->assertConflict($response);

		$this->assertLatestHistoryStatus(JobStatus::SKIPPED);
		$this->assertSkipLogged('night.jpg', 'The photo has been skipped');
	}

	/**
	 * S-114-05: sync import of a duplicate still throws to the caller and records SKIPPED.
	 */
	public function testSyncImportSkipStillThrows(): void
	{
		$caught = null;
		try {
			ImportImageJob::dispatchSync(...$this->importJobArguments(new ImportMode(skip_duplicates: true)));
		} catch (PhotoSkippedException $e) {
			$caught = $e;
		}

		self::assertNotNull($caught);
		$this->assertLatestHistoryStatus(JobStatus::SKIPPED);
		$this->assertSkipLogged('night.jpg', 'The photo has been skipped');
	}

	/**
	 * S-115-43: a scoped import rejects another user's duplicate before resyncing its metadata.
	 */
	public function testScopedImportRejectsOthersDuplicateBeforeResync(): void
	{
		Configs::set('skip_duplicates_not_owned', false);
		$original = $this->originalPhoto();
		$original->iso = null;
		$original->save();

		$caught = $this->importAs($this->userMayUpload1, new ImportMode(skip_duplicates: true, resync_metadata: true, scope_to_owner: true));

		self::assertInstanceOf(PhotoRejectedException::class, $caught);
		$this->assertLatestHistoryStatus(JobStatus::SKIPPED);
		self::assertNull($this->originalPhoto()->iso, 'the other user\'s photo is not resynced');
		self::assertFalse($this->isOriginalInAlbum1());
	}

	/**
	 * S-115-43: a scoped import does not add another user's duplicate to the target album.
	 */
	public function testScopedImportDoesNotLinkOthersDuplicate(): void
	{
		Configs::set('skip_duplicates_not_owned', false);

		$caught = $this->importAs($this->userMayUpload1, new ImportMode(scope_to_owner: true));

		self::assertInstanceOf(PhotoRejectedException::class, $caught);
		$this->assertLatestHistoryStatus(JobStatus::SKIPPED);
		self::assertFalse($this->isOriginalInAlbum1());
	}

	/**
	 * S-115-43: without the scope, another user's duplicate is added to the target album (pre-115 behaviour).
	 */
	public function testUnscopedImportLinksOthersDuplicate(): void
	{
		Configs::set('skip_duplicates_not_owned', false);

		$caught = $this->importAs($this->userMayUpload1, new ImportMode());

		self::assertNull($caught);
		self::assertTrue($this->isOriginalInAlbum1());
	}

	/**
	 * S-114-09: async import of another user's duplicate, rejected by `skip_duplicates_not_owned`,
	 * ends normally and records SKIPPED (Q-115-13).
	 */
	public function testAsyncImportRejectionCompletesWithoutFailure(): void
	{
		Configs::set('skip_duplicates_not_owned', true);
		$job = new ImportImageJob(new NativeLocalFile(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)), $this->userMayUpload1->id, new ImportMode(), null, UserUploadTrustLevel::TRUSTED);
		$job->withFakeQueueInteractions();

		$result = app()->call([$job, 'handle']);

		self::assertNull($result);
		$job->assertNotFailed();
		$job->assertNotReleased();
		$this->assertLatestHistoryStatus(JobStatus::SKIPPED);
		$this->assertSkipLogged('night.jpg', 'The photo has been rejected');
		$this->assertNoErrorLogged();
	}

	/**
	 * S-114-09: async web upload of another user's duplicate, rejected, ends normally and records SKIPPED.
	 */
	public function testAsyncUploadRejectionCompletesWithoutFailure(): void
	{
		Configs::set('skip_duplicates_not_owned', true);
		$this->actingAs($this->userMayUpload1);
		$file = new ProcessableJobFile('.jpg', 'night.jpg');
		$file->write(fopen(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE), 'rb'));
		$file->close();

		$job = new ProcessImageJob($file, null, null);
		$job->withFakeQueueInteractions();

		$result = app()->call([$job, 'handle']);

		self::assertNull($result);
		$job->assertNotFailed();
		$job->assertNotReleased();
		$this->assertLatestHistoryStatus(JobStatus::SKIPPED);
		$this->assertSkipLogged('night.jpg', 'The photo has been rejected');
		$this->assertNoErrorLogged();
	}

	/**
	 * S-114-09: sync import of another user's duplicate still throws to the caller and records SKIPPED.
	 */
	public function testSyncImportRejectionStillThrows(): void
	{
		Configs::set('skip_duplicates_not_owned', true);

		$caught = $this->importAs($this->userMayUpload1, new ImportMode());

		self::assertInstanceOf(PhotoRejectedException::class, $caught);
		$this->assertLatestHistoryStatus(JobStatus::SKIPPED);
		$this->assertSkipLogged('night.jpg', 'The photo has been rejected');
	}

	/**
	 * S-114-09: sync upload of another user's duplicate still answers 409 and records SKIPPED.
	 */
	public function testSyncUploadRejectionStillConflicts(): void
	{
		Configs::set('skip_duplicates_not_owned', true);
		$this->catchFailureSilence = [PhotoRejectedException::class];

		$response = $this->actingAs($this->userMayUpload1)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, file_name: 'night.jpg');

		$this->assertConflict($response);
		$this->assertLatestHistoryStatus(JobStatus::SKIPPED);
		$this->assertSkipLogged('night.jpg', 'The photo has been rejected');
	}

	/**
	 * Import the original's file again as $owner into `album1`, on the sync queue.
	 *
	 * @return PhotoRejectedException|null the rejection, `null` when the import went through
	 */
	private function importAs(User $owner, ImportMode $import_mode): ?PhotoRejectedException
	{
		$this->catchFailureSilence = [PhotoRejectedException::class];
		try {
			ImportImageJob::dispatchSync(new NativeLocalFile(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)), $owner->id, $import_mode, $this->album1, UserUploadTrustLevel::TRUSTED);
		} catch (PhotoRejectedException $e) {
			return $e;
		}

		return null;
	}

	private function isOriginalInAlbum1(): bool
	{
		return DB::table(PA::PHOTO_ALBUM)
			->where(PA::ALBUM_ID, '=', $this->album1->id)
			->where(PA::PHOTO_ID, '=', $this->originalPhoto()->id)
			->exists();
	}

	private function originalPhoto(): Photo
	{
		return Photo::query()->where('title', '=', 'night.jpg')->sole();
	}

	private function makeImportJob(ImportMode $import_mode): ImportImageJob
	{
		return new ImportImageJob(...$this->importJobArguments($import_mode));
	}

	/**
	 * @return array{0:NativeLocalFile,1:int,2:ImportMode,3:null,4:UserUploadTrustLevel}
	 */
	private function importJobArguments(ImportMode $import_mode): array
	{
		return [new NativeLocalFile(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)), $this->admin->id, $import_mode, null, UserUploadTrustLevel::TRUSTED];
	}

	private function assertLatestHistoryStatus(JobStatus $expected): void
	{
		$history = JobHistory::query()->orderByDesc('id')->first();
		self::assertNotNull($history);
		self::assertSame($expected, $history->status);
	}

	private function assertSkipLogged(string $file_name, string $reason): void
	{
		$matches = array_filter(
			$this->logged,
			fn (MessageLogged $e) => $e->level === 'info' && str_contains($e->message, 'Skipped: ' . $file_name) && str_contains($e->message, $reason)
		);
		self::assertCount(1, $matches, 'Expected one info line "Skipped: ' . $file_name . ' (…' . $reason . '…)".');
	}

	private function assertNoErrorLogged(): void
	{
		$errors = array_filter($this->logged, fn (MessageLogged $e) => $e->level === 'error');
		self::assertCount(0, $errors, 'Unexpected error log: ' . implode(' | ', array_map(fn (MessageLogged $e) => $e->message, $errors)));
	}
}
