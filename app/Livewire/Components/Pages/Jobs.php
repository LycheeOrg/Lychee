<?php

namespace App\Livewire\Components\Pages;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\Configs;
use App\Models\JobHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Component;

class Jobs extends Component
{
	/**
	 * We use a computed property instead of attributes
	 * in order to avoid poluting the data sent to the user.
	 *
	 * @return Collection
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function getJobsProperty(): Collection
	{
		return JobHistory::query()
			->orderBy('id', 'desc')
			->limit(Configs::getValueAsInt('log_max_num_line'))
			->get();
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.pages.jobs');
	}

	public function back(): mixed
	{
		return $this->redirect(route('livewire-gallery'));
	}
}
