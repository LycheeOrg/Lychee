<?php

namespace App\Livewire\Components\Pages;

use App\Models\Configs;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
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
		if (!Configs::getValueAsBool('landing_page_enable')) {
			redirect(route('livewire-gallery'));
		}
		$this->title = Configs::getValueAsString('landing_title');
		$this->subtitle = Configs::getValueAsString('landing_subtitle');
		$background = Configs::getValueAsString('landing_background');
		if (!Str::startsWith($background, 'http')) {
			$background = URL::asset($background);
		}
		$this->background = $background;
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
