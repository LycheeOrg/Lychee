<?php

declare(strict_types=1);

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

	/** @var string[] */
	#[Locked] public array $photoIDs;
	/** @var string[] */
	#[Locked] public array $tags = [];
	#[Locked] public int $num;
	public bool $shall_override = false;
	public ?string $tag = '';

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
			$this->tag = implode(', ', $photo->tags);
		}

		Gate::authorize(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $this->photoIDs]);
	}

	/**
	 * Tag.
	 *
	 * @return void
	 */
	public function submit(): void
	{
		$this->tags = collect(explode(',', $this->tag))->map(fn ($v) => trim($v))->filter(fn ($v) => $v !== '')->all();

		Gate::authorize(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $this->photoIDs]);

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
