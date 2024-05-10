<?php

namespace App\Livewire\Components\Menus;

use App\Contracts\Livewire\Params;
use App\Contracts\Models\AbstractAlbum;
use App\Livewire\Components\Pages\Gallery\Album as GalleryAlbum;
use App\Livewire\Traits\InteractWithContextMenu;
use App\Livewire\Traits\InteractWithModal;
use App\Livewire\Traits\Notify;
use App\Models\Album;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * This defines the context menu when right clicking on a single photo.
 */
class PhotoDropdown extends Component
{
	use InteractWithModal;
	use InteractWithContextMenu;
	use Notify;

	/** @var array{albumID:?string,photoID:string} */
	#[Locked] public array $params;
	#[Locked] public bool $is_starred;
	#[Locked] public bool $is_header;
	#[Locked] public bool $is_model_album;
	/**
	 * mount info and load star condition.
	 *
	 * @param array{albumID:?string,photoID:string} $params
	 *
	 * @return void
	 */
	public function mount(array $params): void
	{
		/** @var Album $album */
		$album = Album::query()->find($params[Params::ALBUM_ID]);

		$this->params = $params;
		$this->is_starred = Photo::query()->findOrFail($params[Params::PHOTO_ID])->is_starred;
		$this->is_model_album = $album instanceof Album;
		if ($album !== null) {
			$this->is_header = $album->header_id === $params[Params::PHOTO_ID];
		}
	}

	/**
	 * Renders the Add menu in the top right.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.context-menus.photo-dropdown');
	}

	public function star(): void
	{
		$this->closeContextMenu();
		Gate::authorize(PhotoPolicy::CAN_EDIT_ID, [Photo::class, [$this->params[Params::PHOTO_ID]]]);
		Photo::where('id', '=', $this->params[Params::PHOTO_ID])->update(['is_starred' => true]);
		$this->dispatch('reloadPage')->to(GalleryAlbum::class);
	}

	public function unstar(): void
	{
		$this->closeContextMenu();
		Gate::authorize(PhotoPolicy::CAN_EDIT_ID, [Photo::class, [$this->params[Params::PHOTO_ID]]]);
		Photo::where('id', '=', $this->params[Params::PHOTO_ID])->update(['is_starred' => false]);
		$this->dispatch('reloadPage')->to(GalleryAlbum::class);
	}

	public function tag(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.photo.tag', [Params::PHOTO_ID => $this->params[Params::PHOTO_ID], Params::ALBUM_ID => $this->params[Params::ALBUM_ID]]);
	}

	public function setAsCover(): void
	{
		/** @var Album $album */
		$album = Album::query()->findOrFail($this->params[Params::ALBUM_ID]);

		Gate::authorize(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $album]);
		$album->cover_id = $album->cover_id === $this->params[Params::PHOTO_ID] ? null : $this->params[Params::PHOTO_ID];
		$album->save();
		$this->dispatch('reloadPage')->to(GalleryAlbum::class);
		$this->closeContextMenu();
	}

	public function setAsHeader(): void
	{
		/** @var Album $album */
		$album = Album::query()->findOrFail($this->params[Params::ALBUM_ID]);

		Gate::authorize(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $album]);
		$album->header_id = $album->header_id === $this->params[Params::PHOTO_ID] ? null : $this->params[Params::PHOTO_ID];
		$album->save();
		$this->dispatch('reloadPage')->to(GalleryAlbum::class);
		$this->closeContextMenu();
	}

	public function rename(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.photo.rename', [Params::PHOTO_ID => $this->params[Params::PHOTO_ID], Params::ALBUM_ID => $this->params[Params::ALBUM_ID]]);
	}

	public function copyTo(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.photo.copy-to', [Params::PHOTO_ID => $this->params[Params::PHOTO_ID], Params::ALBUM_ID => $this->params[Params::ALBUM_ID]]);
	}

	public function move(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.photo.move', [Params::PHOTO_ID => $this->params[Params::PHOTO_ID], Params::ALBUM_ID => $this->params[Params::ALBUM_ID]]);
	}

	public function delete(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.photo.delete', [Params::PHOTO_ID => $this->params[Params::PHOTO_ID], Params::ALBUM_ID => $this->params[Params::ALBUM_ID]]);
	}

	public function download(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.photo.download', [Params::PHOTO_ID => $this->params[Params::PHOTO_ID], Params::ALBUM_ID => $this->params[Params::ALBUM_ID]]);
	}
}
