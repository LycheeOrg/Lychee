<?php

namespace App\Livewire\Components\Forms\Add;

use App\Contracts\Models\AbstractAlbum;
use App\Livewire\Traits\InteractWithModal;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

/**
 * This defines the Import From Server modals.
 */
class ImportFromServer extends Component
{
	/**
	 * Allow modal integration.
	 */
	use InteractWithModal;

	/**
	 * We load the parameters.
	 *
	 * @param array{parentID:?string} $params set of parameters of the form
	 *
	 * @return void
	 */
	public function mount(array $params = ['parentID' => null]): void
	{
		Gate::authorize(AlbumPolicy::CAN_IMPORT_FROM_SERVER, AbstractAlbum::class);
	}

	/**
	 * Call the parametrized rendering.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.add.import-from-server');
	}

	/**
	 * Add an handle to close the modal form from a user-land call.
	 *
	 * @return void
	 */
	public function close(): void
	{
		$this->closeModal();
	}
}
