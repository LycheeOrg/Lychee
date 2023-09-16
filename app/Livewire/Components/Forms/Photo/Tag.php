<?php

namespace App\Livewire\Components\Forms\Photo;

use App\Contracts\Livewire\Params;
use App\Livewire\Components\Pages\Gallery\Album as GalleryAlbum;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Tag extends Component
{
	use InteractWithModal;
	use AuthorizesRequests;

	/** @var array<int,string> */
	#[Locked] public array $photoIDs;
	public bool $shall_override = false;
	public ?string $tag = '';
	#[Locked] public array $tags = [];
	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param array{photoID?:string,photoIDs?:array<int,string>,albumID:?string} $params to move
	 *
	 * @return void
	 */
	public function mount(array $params = ['albumID' => null]): void
	{
		$id = $params[Params::PHOTO_ID] ?? null;
		if ($id !== null) {
			$this->photoIDs = [$id];
			/** @var Photo $photo */
			$photo = Photo::query()->findOrFail($id);
			$this->tag = implode(', ', $photo->tags);
		} else {
			$this->photoIDs = $params[Params::PHOTO_IDS] ?? [];
		}
	}

	/**
	 * Tag.
	 *
	 * @return void
	 */
	public function submit(): void
	{
		$this->tags = collect(explode(',', $this->tag))->map(fn ($v, $k) => trim($v))->filter(fn ($v) => $v !== '')->all();

		Gate::check(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $this->photoIDs]);

		/** @var Photo $photo */
		$photos = Photo::query()->whereIn('id', $this->photoIDs)->get();
		foreach ($photos as $photo) {
			if ($this->shall_override) {
				$photo->tags = $this->tags;
			} else {
				$photo->tags = array_unique(array_merge($photo->tags, $this->tags));
			}
			$photo->save();
		}

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
		return view('livewire.forms.photo.tag');
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
