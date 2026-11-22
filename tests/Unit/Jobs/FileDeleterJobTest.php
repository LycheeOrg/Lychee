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

use App\Enum\StorageDiskType;
use App\Exceptions\MediaFileOperationException;
use App\Jobs\FileDeleterJob;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemAdapter as FlysystemAdapter;
use Mockery;
use Psr\Log\LoggerInterface;
use Tests\AbstractTestCase;

class FileDeleterJobTest extends AbstractTestCase
{
	public function testDoesNothingForEmptyFileList(): void
	{
		Storage::fake(StorageDiskType::LOCAL->value);

		(new FileDeleterJob(StorageDiskType::LOCAL, []))->handle();
		self::assertTrue(true);
	}

	public function testDeletesExistingLocalFiles(): void
	{
		Storage::fake(StorageDiskType::LOCAL->value);
		Storage::disk(StorageDiskType::LOCAL->value)->put('foo/bar.jpg', 'data');

		(new FileDeleterJob(StorageDiskType::LOCAL, ['foo/bar.jpg']))->handle();

		Storage::disk(StorageDiskType::LOCAL->value)->assertMissing('foo/bar.jpg');
	}

	public function testSilentlyIgnoresMissingLocalFiles(): void
	{
		Storage::fake(StorageDiskType::LOCAL->value);

		(new FileDeleterJob(StorageDiskType::LOCAL, ['does/not/exist.jpg']))->handle();
		self::assertTrue(true);
	}

	public function testThrowsWhenLocalDeletionFails(): void
	{
		Storage::fake(StorageDiskType::LOCAL->value);
		Storage::disk(StorageDiskType::LOCAL->value)->put('foo/bar.jpg', 'data');

		// Removing write permission on the directory makes `unlink()` fail with a warning
		// that Laravel's error handler promotes to a catchable \ErrorException.
		$root = Storage::disk(StorageDiskType::LOCAL->value)->path('foo');
		chmod($root, 0500);

		try {
			$this->assertThrows(
				fn () => (new FileDeleterJob(StorageDiskType::LOCAL, ['foo/bar.jpg']))->handle(),
				MediaFileOperationException::class
			);
		} finally {
			chmod($root, 0700);
		}
	}

	/**
	 * S-112-01: one summary line per job, no line per file (FR-112-01).
	 */
	public function testLogsOneSummaryLinePerJob(): void
	{
		Storage::fake(StorageDiskType::LOCAL->value);
		Storage::disk(StorageDiskType::LOCAL->value)->put('a.jpg', 'data');
		Storage::disk(StorageDiskType::LOCAL->value)->put('b.jpg', 'data');
		Storage::disk(StorageDiskType::LOCAL->value)->put('c.jpg', 'data');

		$channel = \Mockery::spy(LoggerInterface::class);
		Log::shouldReceive('channel')->with('jobs')->andReturn($channel);

		(new FileDeleterJob(StorageDiskType::LOCAL, ['a.jpg', 'b.jpg', 'c.jpg']))->handle();

		Storage::disk(StorageDiskType::LOCAL->value)->assertMissing(['a.jpg', 'b.jpg', 'c.jpg']);
		$channel->shouldHaveReceived('debug')->once();
		$channel->shouldHaveReceived('debug')->with('Processed 3 paths on images.');
	}

	/**
	 * S-112-02: batches of at most 500 paths, none for an empty list (FR-112-03).
	 */
	public function testBatchesSplitsPathsIntoJobsOfAtMost500(): void
	{
		$paths = array_map(fn (int $i) => 'p/' . $i . '.jpg', range(1, 1201));

		$jobs = FileDeleterJob::batches(StorageDiskType::S3, $paths);

		self::assertCount(3, $jobs);
		self::assertSame([500, 500, 201], array_map(fn (FileDeleterJob $job) => count($job->file_list), $jobs));
		self::assertSame($paths, array_merge(...array_map(fn (FileDeleterJob $job) => $job->file_list, $jobs)));
		foreach ($jobs as $job) {
			self::assertSame(StorageDiskType::S3, $job->storage_type);
		}
		self::assertSame([], FileDeleterJob::batches(StorageDiskType::LOCAL, []));
	}

	/**
	 * S-112-19: on a non-local disk, one delete() per path and no exists() (FR-112-02).
	 */
	public function testDeletesOnNonLocalDiskWithoutExistsCheck(): void
	{
		$disk = $this->mockNonLocalDisk();
		$disk->shouldReceive('delete')->with('a.jpg')->once()->andReturn(true);
		$disk->shouldReceive('delete')->with('b.jpg')->once()->andReturn(true);

		(new FileDeleterJob(StorageDiskType::S3, ['a.jpg', 'b.jpg']))->handle();

		$disk->shouldNotHaveReceived('exists');
	}

	/**
	 * S-112-19: a failed delete() on a non-local disk: every path is still tried, then the job throws (FR-112-02).
	 */
	public function testThrowsAfterTryingEveryPathWhenNonLocalDeletionFails(): void
	{
		$disk = $this->mockNonLocalDisk();
		$disk->shouldReceive('delete')->with('a.jpg')->once()->andReturn(false);
		$disk->shouldReceive('delete')->with('b.jpg')->once()->andReturn(true);

		$this->assertThrows(
			fn () => (new FileDeleterJob(StorageDiskType::S3, ['a.jpg', 'b.jpg']))->handle(),
			MediaFileOperationException::class
		);

		$disk->shouldNotHaveReceived('exists');
	}

	/**
	 * Registers a mocked S3 disk whose adapter is not the local one.
	 *
	 * @return FilesystemAdapter&Mockery\MockInterface
	 */
	private function mockNonLocalDisk(): FilesystemAdapter
	{
		$disk = \Mockery::spy(FilesystemAdapter::class);
		$disk->shouldReceive('getAdapter')->andReturn(\Mockery::mock(FlysystemAdapter::class));
		Storage::set(StorageDiskType::S3->value, $disk);

		return $disk;
	}
}
