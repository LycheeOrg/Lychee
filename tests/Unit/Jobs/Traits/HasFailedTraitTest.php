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

namespace Tests\Unit\Jobs\Traits;

use App\Enum\JobStatus;
use App\Jobs\Traits\HasFailedTrait;
use App\Models\JobHistory;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

class HasFailedTraitTest extends AbstractTestCase
{
	private function makeJobWithHistory(JobHistory $history): object
	{
		return new class($history) {
			use HasFailedTrait;
			use InteractsWithQueue;

			public function __construct(public JobHistory $history)
			{
			}
		};
	}

	public function testMarksHistoryAsFailedAndLogsForRegularException(): void
	{
		// Wrapping a real (unsaved) instance keeps Eloquent's attribute
		// magic intact while letting us intercept `save()` to avoid the DB.
		$history = \Mockery::mock(new JobHistory());
		$history->shouldReceive('save')->once();

		Log::shouldReceive('channel')->once()->with('jobs')->andReturnSelf();
		Log::shouldReceive('error')->once();

		$job = $this->makeJobWithHistory($history);
		$job->failed(new \Exception('boom'));

		self::assertEquals(JobStatus::FAILURE, $history->status);
	}

	public function testReleasesJobWhenExceptionCodeIs999(): void
	{
		$history = \Mockery::mock(new JobHistory());
		$history->shouldReceive('save')->once();

		Log::shouldReceive('channel')->never();

		$job = $this->makeJobWithHistory($history);
		// No underlying queue `$job` is bound, so `release()` is a safe no-op.
		$job->failed(new \Exception('retry me', 999));

		self::assertEquals(JobStatus::FAILURE, $history->status);
	}
}
