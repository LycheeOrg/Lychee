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
use Illuminate\Support\Facades\Log;

/**
 * @property JobHistory $history
 */
trait HasFailedTrait
{
	/**
	 * Catch failures.
	 */
	public function failed(\Throwable $th): void
	{
		if ($th instanceof PhotoSkippedException) {
			// Sync queue: handle() recorded the skip and rethrew it for the caller (ADR-114-01).
			$this->history->status = JobStatus::SKIPPED;
			$this->history->save();

			return;
		}

		$this->history->status = JobStatus::FAILURE;
		$this->history->save();

		if ($th->getCode() === 999) {
			$this->release();
		} else {
			Log::channel('jobs')->error(__LINE__ . ':' . __FILE__ . ' ' . $th->getMessage(), $th->getTrace());
		}
	}
}