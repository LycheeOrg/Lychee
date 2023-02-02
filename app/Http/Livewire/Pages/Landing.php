<?php

namespace App\Http\Livewire\Pages;

use App\Models\Configs;
use Illuminate\View\View;
use Livewire\Component;

class Landing extends Component
{
	public string $title;
	public string $subtitle;
	public string $background;

	/**
	 * @return void
	 */
	public function mount(): void
	{
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
