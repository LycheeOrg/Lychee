<?php

declare(strict_types=1);

namespace App\Livewire\Components\Forms\Photo;

use App\Contracts\Livewire\Params;
use App\Enum\DownloadVariantType;
use App\Livewire\Traits\InteractWithModal;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Download extends Component
{
	use InteractWithModal;
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	/** @var string[] */
	#[Locked] public array $photoIDs;
	public Photo $photo;

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param array{albumID:?string,photoID?:string,photoIDs?:string[]} $params to download
	 *
	 * @return void
	 */
	public function mount(array $params = ['albumID' => null]): void
	{
		$id = $params[Params::PHOTO_ID] ?? null;
		$this->photoIDs = $id !== null ? [$id] : $params[Params::PHOTO_IDS] ?? [];
		$num = count($this->photoIDs);

		if ($num === 1) {
			$this->photo = Photo::query()->findOrFail($this->photoIDs[0]);
			Gate::authorize(PhotoPolicy::CAN_DOWNLOAD, [Photo::class, $this->photo]);
		} else {
			$this->redirect(route('photo_download', ['kind' => DownloadVariantType::ORIGINAL->value]) . '&photoIDs=' . implode(',', $this->photoIDs));
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
