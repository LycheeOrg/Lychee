<?php

namespace App\Livewire\Components\Forms\Add;

use App\Actions\Import\FromServer;
use App\Contracts\Livewire\Params;
use App\Contracts\Models\AbstractAlbum;
use App\Livewire\Forms\ImportFromServerForm;
use App\Livewire\Traits\InteractWithModal;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * This defines the Import From Server modals.
 */
class ImportFromServer extends Component
{
	/**
	 * Allow modal integration.
	 */
	use InteractWithModal;

	public ImportFromServerForm $form;
	private FromServer $fromServer;

	public function boot(): void
	{
		$this->fromServer = resolve(FromServer::class);
	}

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

		$this->form->init($params[Params::PARENT_ID] ?? null);
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

	/**
	 * Hook the submit button.
	 *
	 * @return StreamedResponse
	 */
	public function submit(): StreamedResponse
	{
		Gate::authorize(AlbumPolicy::CAN_IMPORT_FROM_SERVER, AbstractAlbum::class);

		// Empty error bag
		$this->resetErrorBag();

		$this->form->prepare();
		$this->form->validate();

		/** @var int $userId */
		$userId = Auth::id();

		// Validate
		return $this->fromServer->do($this->form->paths, $this->form->getAlbum(), $this->form->getImportMode(), $userId);
	}
}
