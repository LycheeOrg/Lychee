<?php

namespace App\Livewire\Components\Forms\Confirms;

use App\Livewire\Components\Pages\AllSettings;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * This defines the Login Form used in modals.
 */
class SaveAll extends Component
{
	use InteractWithModal;

	/**
	 * Call the parametrized rendering.
	 *
	 * @return View
	 */
	public function render(): View
	{
		Gate::authorize(SettingsPolicy::CAN_EDIT, [Configs::class]);

		return view('livewire.forms.confirms.save-all');
	}

	/**
	 * Hook the submit button.
	 *
	 * @return void
	 */
	public function confirm(): void
	{
		$this->closeModal();
		$this->dispatch('saveAll')->to(AllSettings::class);
	}

	public function close(): void
	{
		$this->closeModal();
	}
}
