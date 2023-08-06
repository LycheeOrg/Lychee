<?php

namespace App\Livewire\Components\Forms\Album;

use App\Livewire\Traits\InteractWithModal;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * This defines the about modal that can be found in the Left Menu.
 */
class Share extends Component
{
	use InteractWithModal;

	/**
	 * Mount the component. We set the attributes here.
	 *
	 * @return void
	 */
	public function mount(): void
	{
	}

	/**
	 * Renders the About component.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.album.share');
	}
}
