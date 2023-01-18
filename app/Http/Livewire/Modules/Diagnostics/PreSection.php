<?php

namespace App\Http\Livewire\Modules\Diagnostics;

use Illuminate\View\View;
use Livewire\Component;

abstract class PreSection extends Component
{
	public string $title;
	public string $error_msg = 'You must have administrator rights to see this.';

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.modules.diagnostics.pre');
	}

	abstract public function getDataProperty(): array;
}
