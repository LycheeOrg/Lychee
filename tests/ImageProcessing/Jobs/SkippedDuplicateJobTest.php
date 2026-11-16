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

use App\DTO\ImportMode;
use App\Enum\JobStatus;
use App\Exceptions\PhotoSkippedException;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\ProcessableJobFile;
use App\Jobs\ImportImageJob;
use App\Jobs\ProcessImageJob;
use App\Models\Configs;
use App\Models\JobHistory;
use App\Models\Photo;
use Illuminate\Log\Events\MessageLogged;
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

	private function originalPhoto(): Photo
	{
		return Photo::query()->where('title', '=', 'night.jpg')->sole();
	}

	private function makeImportJob(ImportMode $import_mode): ImportImageJob
	{
		return new ImportImageJob(...$this->importJobArguments($import_mode));
	}

	/**
	 * @return array{0:NativeLocalFile,1:int,2:ImportMode,3:null}
	 */
	private function importJobArguments(ImportMode $import_mode): array
	{
		return [new NativeLocalFile(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)), $this->admin->id, $import_mode, null];
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
