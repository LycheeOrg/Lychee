<?php

namespace App\Livewire\Components\Forms\Photo;

use App\Contracts\Livewire\Params;
use App\Http\RuleSets\Photo\SetPhotosTitleRuleSet;
use App\Livewire\Components\Pages\Gallery\Album as GalleryAlbum;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Rename extends Component
{
	use InteractWithModal;
	use AuthorizesRequests;

	/** @var string[] */
	#[Locked] public array $photoIDs;
	#[Locked] public int $num;
	public string $title = '';

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param array{photoID?:string,photoIDs?:string[],albumID:?string} $params to move
	 *
	 * @return void
	 */
	public function mount(array $params = ['albumID' => null]): void
	{
		$id = $params[Params::PHOTO_ID] ?? null;
		$this->photoIDs = $id !== null ? [$id] : $params[Params::PHOTO_IDS] ?? [];
		$this->num = count($this->photoIDs);

		if ($this->num === 1) {
			/** @var Photo $photo */
			$photo = Photo::query()->findOrFail($this->photoIDs[0]);
			$this->title = $photo->title;
		}

		Gate::authorize(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $this->photoIDs]);
	}

	/**
	 * Rename.
	 *
	 * @return void
	 */
	public function submit(): void
	{
		$this->validate(SetPhotosTitleRuleSet::rules());
		Gate::authorize(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $this->photoIDs]);
		Photo::query()->whereIn('id', $this->photoIDs)->update(['title' => $this->title]);

		$this->close();
		$this->dispatch('reloadPage')->to(GalleryAlbum::class);
	}

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.photo.rename');
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
