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
use App\Image\Files\NativeLocalFile;
use App\Jobs\ImportImageJob;
use App\Models\Album;
use App\Models\JobHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

/**
 * Only the constructor (JobHistory bookkeeping) is covered here. `handle()`
 * instantiates `App\Actions\Photo\Create` directly (not injected) and
 * performs real image processing, so it is not unit-testable without
 * exercising the full image pipeline.
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

		$job = new ImportImageJob($this->makeFile('/tmp/sunset.jpg'), $user->id, new ImportMode(), $album);

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

		$job = new ImportImageJob($this->makeFile('/tmp/sunset.jpg'), $user->id, new ImportMode(), null);

		self::assertNull($job->album_id);

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertNotNull($history);
		self::assertStringContainsString('added to root', $history->job);
	}
}
