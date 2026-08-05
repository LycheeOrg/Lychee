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
use Illuminate\Support\Facades\Storage;
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
}
