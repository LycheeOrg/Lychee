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

namespace Tests\Unit\Jobs;

use App\DTO\ImportMode;
use App\Enum\JobStatus;
use App\Enum\UserUploadTrustLevel;
use App\Exceptions\ImportFileOutsideRootException;
use App\Factories\AlbumFactory;
use App\Exceptions\PhotoSkippedException;
use App\Image\Files\NativeLocalFile;
use App\Jobs\ImportImageJob;
use App\Models\Album;
use App\Models\JobHistory;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use function Safe\copy;
use function Safe\exec;
use function Safe\mkdir;
use function Safe\realpath;
use function Safe\symlink;
use function Safe\unlink;
use Tests\AbstractTestCase;

/**
 * Only the constructor and `failed()` (JobHistory bookkeeping) are covered here.
 * `handle()` instantiates `App\Actions\Photo\Create` directly (not injected) and
 * performs real image processing; its duplicate-skip paths are covered by
 * `Tests\ImageProcessing\Jobs\SkippedDuplicateJobTest`.
 */
class ImportImageJobTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private function makeFile(string $path = '/tmp/some-photo.jpg'): NativeLocalFile
	{
		$file = \Mockery::mock(NativeLocalFile::class);
		$file->shouldReceive('getPath')->andReturn($path);

		return $file;
	}

	public function testRecordsHistoryWithAlbumTitle(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->with_title('My Vacation')->create();

		$job = new ImportImageJob($this->makeFile('/tmp/sunset.jpg'), $user->id, new ImportMode(), $album, UserUploadTrustLevel::TRUSTED);

		self::assertEquals('/tmp/sunset.jpg', $job->file_path);
		self::assertEquals($album->id, $job->album_id);

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertNotNull($history);
		self::assertStringContainsString('sunset.jpg', $history->job);
		self::assertStringContainsString('My Vacation', $history->job);
	}

	public function testRecordsHistoryWithRootWhenNoAlbum(): void
	{
		$user = User::factory()->create();

		$job = new ImportImageJob($this->makeFile('/tmp/sunset.jpg'), $user->id, new ImportMode(), null, UserUploadTrustLevel::TRUSTED);

		self::assertNull($job->album_id);

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertNotNull($history);
		self::assertStringContainsString('added to root', $history->job);
	}

	/** Feature 115 (FR-115-08, FR-115-18): the payload carries the trust level and the import root. */
	public function testCarriesTrustLevelAndImportRoot(): void
	{
		$user = User::factory()->create();

		$job = new ImportImageJob($this->makeFile('/srv/import/alice/a.jpg'), $user->id, new ImportMode(), null, UserUploadTrustLevel::CHECK, '/srv/import/alice');

		self::assertSame(UserUploadTrustLevel::CHECK, $job->upload_trust_level);
		self::assertSame('/srv/import/alice', $job->import_root);
	}

	/** Feature 115 (S-115-27): a file that left the import root after the scan is not imported. */
	public function testFileSwappedForOutsideLinkFails(): void
	{
		$user = User::factory()->create();
		$base = realpath(sys_get_temp_dir()) . '/lychee-job-' . bin2hex(random_bytes(4));
		mkdir($base . '/alice', 0777, true);
		mkdir($base . '/outside');
		copy(base_path('tests/Samples/night.jpg'), $base . '/outside/secret.jpg');
		copy(base_path('tests/Samples/night.jpg'), $base . '/alice/a.jpg');

		$job = new ImportImageJob(new NativeLocalFile($base . '/alice/a.jpg'), $user->id, new ImportMode(delete_imported: true), null, UserUploadTrustLevel::TRUSTED, $base . '/alice');

		// The user swaps the scanned file for a link to a file outside their folder.
		unlink($base . '/alice/a.jpg');
		symlink($base . '/outside/secret.jpg', $base . '/alice/a.jpg');
		$photos_before = Photo::query()->count();

		try {
			$job->handle(resolve(AlbumFactory::class));
			self::fail('The job should have failed.');
		} catch (ImportFileOutsideRootException) {
			// expected
		} finally {
			$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
			self::assertSame(JobStatus::FAILURE, $history->status);
			self::assertSame($photos_before, Photo::query()->count());
			self::assertFileExists($base . '/outside/secret.jpg');
			self::assertTrue(is_link($base . '/alice/a.jpg'));
			exec('rm -rf ' . escapeshellarg($base));
		}
	}

	/**
	 * S-114-06: on the sync queue handle() rethrows the skip; failed() keeps it a skip.
	 */
	public function testFailedWithSkipRecordsSkipped(): void
	{
		$user = User::factory()->create();
		$job = new ImportImageJob($this->makeFile('/tmp/sunset.jpg'), $user->id, new ImportMode(), null);

		$job->failed(new PhotoSkippedException());

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertNotNull($history);
		self::assertSame(JobStatus::SKIPPED, $history->status);
	}

	/**
	 * S-114-06: any other exception is still a failure.
	 */
	public function testFailedWithOtherErrorRecordsFailure(): void
	{
		$user = User::factory()->create();
		$job = new ImportImageJob($this->makeFile('/tmp/sunset.jpg'), $user->id, new ImportMode(), null);

		$job->failed(new \RuntimeException('boom'));

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertNotNull($history);
		self::assertSame(JobStatus::FAILURE, $history->status);
	}
}
