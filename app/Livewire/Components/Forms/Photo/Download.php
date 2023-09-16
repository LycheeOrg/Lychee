<?php

namespace App\Livewire\Components\Forms\Photo;

use App\Contracts\Livewire\Params;
use App\Enum\DownloadVariantType;
use App\Livewire\Traits\InteractWithModal;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Photo;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Download extends Component
{
	use InteractWithModal;
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	/** @var array<int,string> */
	#[Locked] public array $photoIDs;
	public Photo $photo;

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param array{albumID:?string,photoID?:string,photoIDs?:array<int,string>} $params to download
	 *
	 * @return void
	 */
	public function mount(array $params = ['albumID' => null]): void
	{
		$id = $params[Params::PHOTO_ID] ?? null;
		if ($id !== null) {
			$this->photoIDs = [$id];
			$this->photo = Photo::query()->findOrFail($id);
		} else {
			$this->photoIDs = $params[Params::PHOTO_IDS] ?? null;
			$this->redirect(route('photo_download',
				['photoIDs' => $this->photoIDs,
					'kind' => DownloadVariantType::ORIGINAL->value]));
		}
	}

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.photo.download');
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
