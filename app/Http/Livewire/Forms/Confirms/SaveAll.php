<?php

namespace App\Http\Livewire\Forms\Confirms;

use App\Http\Livewire\Traits\InteractWithModal;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * This defines the Login Form used in modals.
 */
class SaveAll extends Component
{
	use InteractWithModal;

	/**
	 * Call the parametrized rendering.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.confirms.save-all');
	}

	/**
	 * Hook the submit button.
	 *
	 * @return void
	 */
	public function confirm(): void
	{
		$this->closeModal();
		$this->emitTo('pages.all-settings', 'saveAll');
	}

	public function close(): void
	{
		$this->closeModal();
	}
}
