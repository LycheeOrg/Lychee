<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use Illuminate\Queue\Middleware\Skip;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * "Latest job wins" debounce: when several instances of the same job are
 * queued for the same target in quick succession (e.g. repeated saves to
 * the same album), only the most recently queued one actually runs -
 * earlier, now-superseded instances skip themselves via {@see middleware()}.
 *
 * @property string $jobId
 */
trait DebouncesLatestJobTrait
{
	/**
	 * Cache key this job instance is debounced under, e.g. an album id, or
	 * a composite key such as "{kind}:{album_id}".
	 */
	abstract protected function latestJobCacheKey(): string;

	/**
	 * Short label identifying the job's target for log messages, e.g.
	 * "album 123".
	 */
	abstract protected function latestJobLogContext(): string;

	/**
	 * Call from the constructor to register this instance as the latest
	 * queued job for {@see latestJobCacheKey()}. Any older, still-queued
	 * instance for the same key will then skip itself.
	 */
	protected function registerAsLatestJob(): void
	{
		$this->jobId = uniqid('job_', true);

		Cache::put(
			$this->latestJobCacheKey(),
			$this->jobId,
			ttl: now()->plus(days: 1)
		);
	}

	/**
	 * Get the middleware the job should pass through.
	 *
	 * @return array<int,object>
	 */
	public function middleware(): array
	{
		return [
			Skip::when(fn () => $this->hasNewerJobQueued()),
		];
	}

	protected function hasNewerJobQueued(): bool
	{
		$latest_job_id = Cache::get($this->latestJobCacheKey());

		// We skip if there is a newer job queued (latest job ID is different from this one)
		$has_newer_job = $latest_job_id !== null && $latest_job_id !== $this->jobId;
		if ($has_newer_job) {
			Log::channel('jobs')->debug("Skipping job {$this->jobId} for {$this->latestJobLogContext()} due to newer job {$latest_job_id} queued.");
		}

		return $has_newer_job;
	}

	/**
	 * Call at the start of `handle()` so a subsequent save can register a
	 * fresh "latest job" once this run actually starts executing.
	 */
	protected function forgetLatestJobMarker(): void
	{
		Cache::forget($this->latestJobCacheKey());
	}
}
