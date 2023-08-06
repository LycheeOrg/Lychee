<?php

namespace App\Livewire\Components\Pages;

use App\Livewire\Forms\AllConfigsForms;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class AllSettings extends Component
{
	/**
	 * Add interaction with modal.
	 */
	use InteractWithModal;

	public AllConfigsForms $form;

	/**
	 * Mount the component of the front-end.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		Gate::authorize(SettingsPolicy::CAN_EDIT, [Configs::class]);

		$this->form->setConfigs(Configs::orderBy('cat', 'asc')->get());
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.pages.all-settings');
	}

	/**
	 * Open Saving confirmation modal.
	 *
	 * @return void
	 */
	public function openConfirmSave(): void
	{
		$this->openModal('forms.confirms.save-all');
	}

	/**
	 * Save everything.
	 *
	 * @return void
	 */
	#[On('saveAll')]
	public function saveAll(): void
	{
		Gate::authorize(SettingsPolicy::CAN_EDIT, [Configs::class]);

		$this->form->save();
	}

	public function back(): mixed
	{
		return $this->redirect(route('settings'));
	}
}
