<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Enum\JobStatus;
use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Models\JobHistory;
use Illuminate\Routing\Controller;

/**
 * Sometimes the job history is a bit messed up,
 * this happens when there are crashes or error in the logic.
 *
 * In theory this should not be needed but if this is not resolved
 * the pulse feedback would always stay alive.
 */
class FixJobs extends Controller
{
	/** @var JobStatus[] */
	private array $waitingJobsTypes = [JobStatus::READY, JobStatus::STARTED];

	/**
	 * Fix alls jobs that are in waiting states and mark them as failures.
	 *
	 * @return void
	 */
	public function do(MaintenanceRequest $_request): void
	{
		if ($this->check($_request) === 0) {
			return;
		}

		JobHistory::query()
			->whereIn('status', $this->waitingJobsTypes)
			->update(['status' => JobStatus::FAILURE]);
	}

	/**
	 * Check if there are any waiting jobs.
	 * If not, we will not display the module to reduce complexity.
	 *
	 * @return int
	 */
	public function check(MaintenanceRequest $_request): int
	{
		return JobHistory::whereIn('status', $this->waitingJobsTypes)->count();
	}
}
