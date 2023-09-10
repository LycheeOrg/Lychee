<?php

namespace App\Livewire\Components\Forms\Photo;

use App\Actions\Photo\Delete as DeleteAction;
use App\Http\RuleSets\Photo\DeletePhotosRuleSet;
use App\Http\RuleSets\Photo\MovePhotosRuleSet;
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

	/** @var array<int,string> */
	#[Locked]
	public array $photoIDs;

	#[Locked]
	public string $albumId;

	#[Locked]
	public string $title;

	// Destination
	public ?string $albumID = null;
	public ?string $albumTitle = null;

	public ?string $search = null; // ! wired

	public array $albumListSaved;
	
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
		$this->title = Photo::query()->findOrFail($id)->title;
		$this->albumId = $params['albumId'];
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
	 * Execute deletion.
	 *
	 * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
	 */
	public function submit(DeleteAction $delete): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
	{
		$this->validate(MovePhotosRuleSet::rules());
		Gate::check(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $this->photoIDs]);

		$fileDeleter = $delete->do($this->photoIDs);
		App::terminating(fn () => $fileDeleter->do());

		return redirect()->to(route('livewire-gallery-album', ['albumId' => $this->albumId]));
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
