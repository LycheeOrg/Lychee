<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs\Traits;

use App\Enum\JobStatus;
use App\Exceptions\PhotoSkippedException;
use App\Models\JobHistory;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Support\Facades\Log;

/**
 * A photo skipped as a duplicate is the expected outcome of `skip_duplicates`, not a failure (ADR-114-01).
 *
 * @property JobHistory                           $history
 * @property \Illuminate\Contracts\Queue\Job|null $job
 */
trait HandlesSkippedPhotoTrait
{
	/**
	 * Record the skip.
	 *
	 * On an asynchronous queue the job then ends normally: one attempt, no failed_jobs row.
	 * On the sync queue, or without a queue job, the exception is rethrown so that
	 * the caller (upload 409, import report) still sees it.
	 *
	 * @throws PhotoSkippedException
	 */
	protected function skipped(PhotoSkippedException $e, string $file_name): void
	{
		$this->history->status = JobStatus::SKIPPED;
		$this->history->save();
		Log::channel('jobs')->info(sprintf('Skipped: %s (%s)', $file_name, $e->getMessage()));

		if ($this->callerAwaitsOutcome()) {
			throw $e;
		}
	}

	private function callerAwaitsOutcome(): bool
	{
		return $this->job === null || $this->job instanceof SyncJob;
	}
}
