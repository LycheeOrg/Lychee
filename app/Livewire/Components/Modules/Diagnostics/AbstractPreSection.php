<?php

namespace App\Livewire\Components\Modules\Diagnostics;

use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Basic pre section to display the diagnostics parts.
 */
abstract class AbstractPreSection extends Component
{
	#[Locked] public string $title;
	#[Locked] public string $error_msg = 'Error: You must have administrator rights to see this.';
	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	final public function render(): View
	{
		return view('livewire.modules.diagnostics.pre');
	}

	/**
	 * Defined the data to be displayed.
	 *
	 * @return array
	 */
	abstract public function getDataProperty(): array;
}
