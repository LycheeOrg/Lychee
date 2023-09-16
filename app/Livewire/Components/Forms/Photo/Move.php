<?php

namespace App\Livewire\Components\Forms\Photo;

use App\Actions\User\Notify as UserNotify;
use App\Contracts\Livewire\Params;
use App\Enum\SmartAlbumType;
use App\Http\RuleSets\Photo\MovePhotosRuleSet;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Album;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Move extends Component
{
	use InteractWithModal;
	use AuthorizesRequests;

	#[Locked] public string $parent_id;
	/** @var array<int,string> */
	#[Locked] public array $photoIDs;
	#[Locked] public string $title = '';
	// Destination
	#[Locked] public ?string $albumID = null;
	#[Locked] public ?string $albumTitle = null;
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
			$this->title = $photo->title;
		} else {
			$this->photoIDs = $params[Params::PHOTO_IDS] ?? [];
		}

		$this->parent_id = $params[Params::ALBUM_ID] ?? SmartAlbumType::UNSORTED->value;
	}

	/**
	 * Prepare confirmation step.
	 *
	 * @param string $id
	 * @param string $title
	 *
	 * @return void
	 */
	public function setAlbum(string $id, string $title): void
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

		// We stay in current album.
		$this->redirect(route('livewire-gallery-album', ['albumId' => $this->parent_id]), true);
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
