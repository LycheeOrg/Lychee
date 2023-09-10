<?php

namespace App\Livewire\Components\Forms\Photo;

use App\Actions\User\Notify as UserNotify;
use App\Enum\SmartAlbumType;
use App\Http\RuleSets\Photo\MovePhotosRuleSet;
use App\Livewire\Traits\InteractWithModal;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Album;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class Move extends Component
{
	use InteractWithModal;
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	/** @var array<int,string> */
	#[Locked] public array $photoIDs;
	#[Locked] public ?string $albumId;
	#[Locked] public string $title;
	// Destination
	#[Locked] public ?string $albumID = null;
	#[Locked] public ?string $albumTitle = null;
	#[Locked] public ?string $parent_id;
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
		$this->parent_id = $photo->album_id;
	}

	/**
	 * Prepare confirmation step.
	 *
	 * @param string|null $id
	 * @param string      $title
	 *
	 * @return RedirectResponse|Redirector
	 */
	public function setAlbum(string $id, string $title): RedirectResponse|Redirector
	{
		$this->albumID = $id;
		$this->title = $title;

		$this->validate(MovePhotosRuleSet::rules());
		Gate::check(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $this->photoIDs]);

		$this->albumID = $this->albumID === '' ? null : $this->albumID;

		/** @var ?Album $album */
		$album = $this->albumID === null ? null : Album::query()->findOrFail($this->albumID);
		Gate::check(AlbumPolicy::CAN_EDIT, [Album::class, $album]);

		$photos = Photo::query()->findOrFail($this->photoIDs);

		$notify = new UserNotify();

		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$photo->album_id = $album?->id;
			// Avoid unnecessary DB request, when we access the album of a
			// photo later (e.g. when a notification is sent).
			$photo->setRelation('album', $album);
			if ($album !== null) {
				$photo->owner_id = $album->owner_id;
			}
			$photo->save();
			$notify->do($photo);
		}

		return redirect()->to(route('livewire-gallery-album', ['albumId' => $this->albumID ?? SmartAlbumType::UNSORTED->value]));
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
