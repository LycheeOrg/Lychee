<?php

namespace App\Http\Livewire\Pages;

use App\Enum\Livewire\PageMode;
use App\Http\Livewire\Traits\InteractWithModal;
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
