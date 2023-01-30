<?php

namespace App\Http\Livewire\Pages;

use App\Enum\Livewire\PageMode;
use App\Models\Configs;
use Illuminate\View\View;
use Livewire\Component;

class Landing extends Component
{
	public PageMode $mode = PageMode::LANDING;

	/**
	 * @return void
	 */
	public function mount(): void
	{
		if (!Configs::getValueAsBool('landing_page_enable')) {
			// TODO: redirect to route('livewire_index', ['page' => PageMode::GALLERY->value]);
		}

		$this->title = Configs::getValueAsString('landing_title');
		$this->subtitle = Configs::getValueAsString('landing_subtitle');
		$this->background = Configs::getValueAsString('landing_background');
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.pages.landing');
	}
}
