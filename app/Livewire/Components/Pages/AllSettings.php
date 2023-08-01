<?php

namespace App\Livewire\Components\Pages;

use App\Contracts\Livewire\Reloadable;
use App\Enum\Livewire\PageMode;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class AllSettings extends Component implements Reloadable
{
	/*
	* Add interaction with modal
	*/
	use InteractWithModal;

	public Collection $configs;

	// public PageMode $mode = PageMode::ALL_SETTINGS;

	/**
	 * This allows Livewire to know which values of the $configs we
	 * want to display in the wire:model. Sort of a white listing.
	 *
	 * @var array<string,string>
	 */
	protected $rules = [
		'configs.*.value' => 'required',
	];

	/**
	 * Listeners for saving all data. This is because we use a modal pop-up to confirm.
	 * One way to avoid this listener would be to incorporate the modal into this component (but I don't feel like it).
	 *
	 * @var string[]
	 */
	protected $listeners = [
		'saveAll',
	];

	/**
	 * Mount the component of the front-end.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		Gate::authorize(SettingsPolicy::CAN_EDIT, [Configs::class]);

		$this->configs = Configs::orderBy('cat', 'asc')->get();
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
	public function saveAll(): void
	{
		Gate::authorize(SettingsPolicy::CAN_EDIT, [Configs::class]);

		foreach ($this->configs as $config) {
			$config->save();
		}
	}

	public function back(): mixed
	{
		return $this->redirect(route('settings'));
	}

	#[On('reloadPage')]
	public function reloadPage(): void
	{
		$this->back();
	}
}
