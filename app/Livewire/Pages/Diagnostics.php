<?php

namespace App\Livewire\Pages;

use Illuminate\View\View;
use Livewire\Component;

class Diagnostics extends Component
{
	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.pages.diagnostics');
	}

	public function back(): mixed
	{
		return $this->redirect(route('livewire-gallery'));
	}
}
