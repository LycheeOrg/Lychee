<?php

namespace App\Livewire\Pages;

use App\Enum\Livewire\PageMode;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class Settings extends Component
{
	/*
	* Add interaction with modal
	*/
	use InteractWithModal;

	public PageMode $mode = PageMode::SETTINGS;

	/**
	 * Mount the component of the front-end.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		Gate::authorize(SettingsPolicy::CAN_EDIT, [Configs::class]);
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.pages.settings');
	}

	/**
	 * Open Settings page.
	 *
	 * @return void
	 */
	public function openAllSettings(): void
	{
		$this->emitTo('index', 'openPage', PageMode::ALL_SETTINGS->value);
	}
}
