<?php

declare(strict_types=1);

namespace App\Livewire\Components\Forms\Photo;

use App\Actions\Photo\Delete as DeleteAction;
use App\Contracts\Livewire\Params;
use App\Enum\SmartAlbumType;
use App\Http\RuleSets\Photo\DeletePhotosRuleSet;
use App\Livewire\Traits\InteractWithModal;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Delete extends Component
{
	use InteractWithModal;
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	/** @var string[] */
	#[Locked] public array $photoIDs;
	#[Locked] public ?string $albumId = null;
	#[Locked] public string $title = '';
	#[Locked] public int $num;
	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param array{albumID:?string,photoID?:string,photoIDs?:string[]} $params to delete
	 *
	 * @return void
	 */
	public function mount(array $params = ['albumID' => null]): void
	{
		$id = $params[Params::PHOTO_ID] ?? null;
		$this->photoIDs = $id !== null ? [$id] : $params[Params::PHOTO_IDS] ?? [];

		Gate::authorize(PhotoPolicy::CAN_DELETE_BY_ID, [Photo::class, $this->photoIDs]);

		$this->num = count($this->photoIDs);

		if ($this->num === 1) {
			/** @var Photo $photo */
			$photo = Photo::query()->findOrFail($this->photoIDs[0]);
			$this->title = $photo->title;
		}

		$this->albumId = $params[Params::ALBUM_ID] ?? null;
		$this->num = count($this->photoIDs);
	}

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.photo.delete');
	}

	/**
	 * Execute deletion.
	 *
	 * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
	 */
	public function submit(DeleteAction $delete): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
	{
		$this->validate(DeletePhotosRuleSet::rules());

		Gate::authorize(PhotoPolicy::CAN_DELETE_BY_ID, [Photo::class, $this->photoIDs]);

		$fileDeleter = $delete->do($this->photoIDs);
		App::terminating(fn () => $fileDeleter->do());

		return redirect()->to(route('livewire-gallery-album', ['albumId' => $this->albumId ?? SmartAlbumType::UNSORTED->value]));
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
