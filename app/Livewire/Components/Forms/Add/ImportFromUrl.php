<?php

declare(strict_types=1);

namespace App\Livewire\Components\Forms\Add;

use App\Actions\Import\FromUrl;
use App\Contracts\Livewire\Params;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\SmartAlbumType;
use App\Exceptions\MassImportException;
use App\Livewire\Components\Pages\Gallery\Album as PageGalleryAlbum;
use App\Livewire\Forms\ImportFromUrlForm;
use App\Livewire\Traits\InteractWithModal;
use App\Livewire\Traits\Notify;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

/**
 * This defines the Import From Url modals.
 */
class ImportFromUrl extends Component
{
	/**
	 * Allow modal integration.
	 */
	use InteractWithModal;
	use Notify;

	private FromUrl $fromUrl;

	public function boot(): void
	{
		$this->fromUrl = resolve(FromUrl::class);
	}

	public ImportFromUrlForm $form;

	/**
	 * We load the parameters.
	 *
	 * @param array{parentID:?string} $params set of parameters of the form
	 *
	 * @return void
	 */
	public function mount(array $params = ['parentID' => null]): void
	{
		$albumId = $params[Params::PARENT_ID] ?? null;

		// remove smart albums => if we are in one: upload to unsorted (i.e. albumId = null)
		if (SmartAlbumType::tryFrom($albumId) !== null) {
			$albumId = null;
		}

		/** @var Album $album */
		$album = $albumId === null ? null : Album::query()->findOrFail($albumId);

		Gate::authorize(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $album]);

		$this->form->init($albumId);
	}

	/**
	 * Call the parametrized rendering.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.add.import-from-url');
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
	 * A form has a Submit method.
	 *
	 * @return void
	 */
	public function submit(): void
	{
		// Reset error bag
		$this->resetErrorBag();

		$this->form->prepare();
		$this->form->validate();

		Gate::authorize(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $this->form->getAlbum()]);

		/** @var int $userId */
		$userId = Auth::id();

		try {
			$this->fromUrl->do($this->form->urls, $this->form->getAlbum(), $userId);
			$this->notify(__('lychee.UPLOAD_IMPORT_COMPLETE'));
		} catch (MassImportException $e) {
			$this->notify($e->getMessage());
		}
		// Do we want refresh or direcly open newly created Album ?
		$this->dispatch('reloadPage')->to(PageGalleryAlbum::class);
		$this->close();
	}
}
