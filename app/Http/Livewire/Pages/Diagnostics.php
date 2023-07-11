<?php

namespace App\Http\Livewire\Pages;

use App\Enum\Livewire\PageMode;
use Illuminate\View\View;
use Livewire\Component;

class Diagnostics extends Component
{
	public PageMode $mode = PageMode::DIAGNOSTICS;

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.pages.diagnostics');
	}
}
