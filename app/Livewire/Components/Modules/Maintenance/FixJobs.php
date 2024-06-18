<?php

declare(strict_types=1);

namespace App\Livewire\Components\Modules\Maintenance;

use App\Enum\JobStatus;
use App\Models\Configs;
use App\Models\JobHistory;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Sometimes the job history is a bit messed up,
 * this happens when there are crashes or error in the logic.
 *
 * In theory this should not be needed but if this is not resolved
 * the pulse feedback would always stay alive.
 */
class FixJobs extends Component
{
	/** @var JobStatus[] */
	private array $waitingJobsTypes = [JobStatus::READY, JobStatus::STARTED];

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);

		return view('livewire.modules.maintenance.fix-jobs');
	}

	/**
	 * Fix alls jobs that are in waiting states and mark them as failures.
	 *
	 * @return void
	 */
	public function do(): void
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);

		if ($this->getNoWaitingJobsFoundProperty()) {
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
	 * @return bool
	 */
	public function getNoWaitingJobsFoundProperty(): bool
	{
		return JobHistory::whereIn('status', $this->waitingJobsTypes)->count() === 0;
	}
}
