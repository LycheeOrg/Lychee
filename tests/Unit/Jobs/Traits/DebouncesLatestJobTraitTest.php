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

use App\Jobs\Traits\DebouncesLatestJobTrait;
use Illuminate\Queue\Middleware\Skip;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

class DebouncesLatestJobTraitTest extends AbstractTestCase
{
	private function makeJob(string $cache_key = 'debounce-test-key'): object
	{
		return new class($cache_key) {
			use DebouncesLatestJobTrait;

			public function __construct(private string $cache_key)
			{
				$this->registerAsLatestJob();
			}

			protected function latestJobCacheKey(): string
			{
				return $this->cache_key;
			}

			protected function latestJobLogContext(): string
			{
				return 'test target';
			}

			public function callHasNewerJobQueued(): bool
			{
				return $this->hasNewerJobQueued();
			}

			public function callForgetLatestJobMarker(): void
			{
				$this->forgetLatestJobMarker();
			}

			public function getJobId(): string
			{
				return $this->jobId;
			}
		};
	}

	public function testRegisterAsLatestJobStoresItsOwnIdAsLatest(): void
	{
		$job = $this->makeJob();

		self::assertEquals($job->getJobId(), Cache::get('debounce-test-key'));
	}

	public function testMiddlewareContainsASkip(): void
	{
		$job = $this->makeJob();

		$middleware = $job->middleware();
		self::assertCount(1, $middleware);
		self::assertInstanceOf(Skip::class, $middleware[0]);
	}

	public function testHasNewerJobQueuedIsFalseWhenStillLatest(): void
	{
		$job = $this->makeJob();

		self::assertFalse($job->callHasNewerJobQueued());
	}

	public function testHasNewerJobQueuedIsTrueWhenSupersededAndLogs(): void
	{
		$job = $this->makeJob('debounce-superseded-key');

		// A second registration for the same key supersedes the first job.
		$newer = $this->makeJob('debounce-superseded-key');

		Log::shouldReceive('channel')->once()->with('jobs')->andReturnSelf();
		Log::shouldReceive('debug')->once();

		self::assertTrue($job->callHasNewerJobQueued());
		self::assertFalse($newer->callHasNewerJobQueued());
	}

	public function testHasNewerJobQueuedIsFalseWhenMarkerForgotten(): void
	{
		$job = $this->makeJob('debounce-forgotten-key');

		$job->callForgetLatestJobMarker();

		self::assertFalse($job->callHasNewerJobQueued());
	}
}
