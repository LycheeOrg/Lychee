<?php

namespace App\Livewire\Components\Forms\Photo;

use App\Livewire\Traits\InteractWithModal;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Photo;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Delete extends Component
{
	use InteractWithModal;
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	/** @var array<int,string> */
	#[Locked] public array $photoIDs;
	#[Locked] public string $albumId;
	#[Locked] public string $title;
	// Destination
	#[Locked] public ?string $albumID = null;
	#[Locked] public ?string $albumTitle = null;
	#[Locked] public ?string $parent_id;
	#[Locked] public ?int $lft;
	#[Locked] public ?int $rgt;
	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param array $params to delete
	 *
	 * @return void
	 */
	public function mount(array $params = []): void
	{
		/** @var string $id */
		$id = $params['photoId'];
		$this->photoIDs = [$id];
		$photo = Photo::query()->findOrFail($id);
		$this->title = $photo->title;
		$this->lft = $photo?->album?->lft;
		$this->rgt = $photo?->album?->rgt;
		$this->parent_id = $photo?->album?->parent_id;
	}

	/**
	 * Prepare confirmation step.
	 *
	 * @param string $id
	 * @param string $title
	 *
	 * @return void
	 */
	public function setAlbum(string $id, string $title)
	{
		$this->albumID = $id;

		$this->title = $title;
	}

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.photo.move');
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
