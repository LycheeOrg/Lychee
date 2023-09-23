<?php

namespace App\Livewire\Components\Pages;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Livewire\Components\Menus\LeftMenu;
use App\Models\Configs;
use App\Models\JobHistory;
use App\Policies\SettingsPolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class Jobs extends Component
{
	/**
	 * Mount the component of the front-end.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		Gate::authorize(SettingsPolicy::CAN_SEE_LOGS, [Configs::class]);
	}

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
		$this->dispatch('closeLeftMenu')->to(LeftMenu::class);

		return $this->redirect(route('livewire-gallery'), true);
	}
}
