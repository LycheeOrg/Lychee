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

	/**
	 * Return error message because we don't want this serialized.
	 *
	 * @return string
	 */
	public function getErrorMessageProperty(): string
	{
		return 'Error: You must have administrator rights to see this.';
	}
}
