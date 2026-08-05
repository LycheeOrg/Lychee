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

use App\Enum\JobStatus;
use App\Facades\Helpers;
use App\Jobs\CleanUpExtraction;
use App\Models\JobHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

class CleanUpExtractionTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testRemovesEmptyDirectoryAndMarksSuccess(): void
	{
		$user = User::factory()->create();
		$dir = sys_get_temp_dir() . '/lychee-cleanup-test-' . uniqid();
		mkdir($dir);

		Helpers::shouldReceive('remove_dir')->once()->with($dir);

		try {
			$job = new CleanUpExtraction($dir, $user->id);
			$job->handle();

			$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
			self::assertNotNull($history);
			self::assertEquals(JobStatus::SUCCESS, $history->status);
			self::assertStringContainsString('Removing', $history->job);
		} finally {
			if (is_dir($dir)) {
				rmdir($dir);
			}
		}
	}

	public function testLeavesNonEmptyDirectoryAndMarksFailure(): void
	{
		$user = User::factory()->create();
		$dir = sys_get_temp_dir() . '/lychee-cleanup-test-' . uniqid();
		mkdir($dir);
		$leftover_file = $dir . '/leftover.txt';
		file_put_contents($leftover_file, 'still here');

		try {
			$job = new CleanUpExtraction($dir, $user->id);
			$job->handle();

			$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
			self::assertNotNull($history);
			self::assertEquals(JobStatus::FAILURE, $history->status);
			self::assertFileExists($leftover_file);
		} finally {
			unlink($leftover_file);
			rmdir($dir);
		}
	}
}
