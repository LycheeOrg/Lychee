<?php

namespace App\Livewire\Components\Modules\Jobs;

use App\Enum\JobStatus;
use App\Enum\OrderSortingType;
use App\Models\Configs;
use App\Models\JobHistory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Feedback extends Component
{
	#[Locked] public bool $display = false;
	#[Locked] public bool $open = false;
	/**
	 * Mount the component.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		if (!Auth::check()) {
			$this->display = false;

			return;
		}

		$this->open = Configs::getValueAsBool('current_job_processing_visible');
		$this->display = true;
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.modules.jobs.feedback');
	}

	/**
	 * Return the list of current Jobs that will be created & possibly processed.
	 *
	 * @return Collection Jobs
	 */
	public function getJobHistoryProperty(): Collection
	{
		return JobHistory::query()
			->where('owner_id', '=', Auth::id())
			->whereIn('status', [JobStatus::READY, JobStatus::STARTED])
			->orderBy('status', OrderSortingType::DESC->value)
			->orderBy('created_at', OrderSortingType::ASC->value)
			->get();
	}

	/**
	 * Return the Number of jobs ready.
	 *
	 * @return int
	 */
	public function getNumReadyProperty(): int
	{
		return JobHistory::query()
			->where('owner_id', '=', Auth::id())
			->where('status', '=', JobStatus::READY)
			->count();
	}

	/**
	 * Return the Number of jobs ready.
	 *
	 * @return int
	 */
	public function getNumStartedProperty(): int
	{
		return JobHistory::query()
			->where('owner_id', '=', Auth::id())
			->where('status', '=', JobStatus::STARTED)
			->count();
	}
}
