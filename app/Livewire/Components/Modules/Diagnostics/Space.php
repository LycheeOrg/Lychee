<?php

namespace App\Livewire\Components\Modules\Diagnostics;

use App\Actions\Diagnostics\Space as DiagnosticsSpace;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Space extends Component
{
	#[Locked] public string $title = 'Space Usage';
	#[Locked] public array $space = [];
	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.modules.diagnostics.space');
	}

	/**
	 * Return the size used by Lychee.
	 * We now separate this from the initial get() call as this is quite time consuming.
	 *
	 * @return void
	 */
	public function getSize(DiagnosticsSpace $space): void
	{
		$this->space = $space->get();
	}
}
