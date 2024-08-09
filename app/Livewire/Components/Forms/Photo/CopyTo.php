<?php

namespace App\Livewire\Components\Forms\Photo;

use App\Actions\Photo\Duplicate;
use App\Actions\User\Notify as UserNotify;
use App\Contracts\Livewire\Params;
use App\Enum\SmartAlbumType;
use App\Legacy\V1\RuleSets\Photo\DuplicatePhotosRuleSet;
use App\Livewire\Traits\InteractWithModal;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Album;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CopyTo extends Component
{
	use InteractWithModal;
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	private Duplicate $duplicate;

	#[Locked] public string $parent_id;
	/** @var string[] */
	#[Locked] public array $photoIDs;
	#[Locked] public string $title = '';
	// Destination
	#[Locked] public ?string $albumID = null;
	#[Locked] public ?string $albumTitle = null;
	#[Locked] public int $num;
	/**
	 * Boot method.
	 */
	public function boot(): void
	{
		$this->duplicate = resolve(Duplicate::class);
	}

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

		$this->validate(DuplicatePhotosRuleSet::rules());
		Gate::authorize(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $this->photoIDs]);

		$this->albumID = $this->albumID === '' ? null : $this->albumID;

		/** @var ?Album $album */
		$album = $this->albumID === null ? null : Album::query()->findOrFail($this->albumID);
		Gate::authorize(AlbumPolicy::CAN_EDIT, [Album::class, $album]);

		$photos = Photo::query()->with(['size_variants'])->findOrFail($this->photoIDs);

		$copiedPhotos = $this->duplicate->do($photos, $album);

		$notify = new UserNotify();

		$copiedPhotos->each(fn ($photo, $k) => $notify->do($photo));

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
		return view('livewire.forms.photo.copyTo');
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
