<?php

namespace App\Livewire\Components\Menus;

use App\Contracts\Livewire\Params;
use App\Livewire\Components\Pages\Gallery\Album as GalleryAlbum;
use App\Livewire\Traits\InteractWithContextMenu;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * This defines the context menu when right clicking on multiple photo.
 */
class PhotosDropdown extends Component
{
	use InteractWithModal;
	use InteractWithContextMenu;

	/** @var array{albumID:?string,photoIDs:array<int,string>} */
	#[Locked] public array $params;
	#[Locked] public bool $are_starred;
	/**
	 * mount info and load star condition.
	 *
	 * @param array{albumID:?string,photoIDs:array<int,string>} $params
	 *
	 * @return void
	 */
	public function mount(array $params): void
	{
		$this->params = $params;
		$this->are_starred = count($params[Params::PHOTO_IDS]) ===
			Photo::query()->whereIn('id', $params[Params::PHOTO_IDS])->where('is_starred', '=', true)->count();
	}

	/**
	 * Renders the Add menu in the top right.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.context-menus.photos-dropdown');
	}

	public function starAll(): void
	{
		$this->closeContextMenu();
		Gate::authorize(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $this->params[Params::PHOTO_IDS]]);
		Photo::whereIn('id', $this->params[Params::PHOTO_IDS])->update(['is_starred' => true]);
		$this->dispatch('reloadPage')->to(GalleryAlbum::class);
	}

	public function unstarAll(): void
	{
		$this->closeContextMenu();
		Gate::authorize(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $this->params[Params::PHOTO_IDS]]);
		Photo::whereIn('id', $this->params[Params::PHOTO_IDS])->update(['is_starred' => false]);
		$this->dispatch('reloadPage')->to(GalleryAlbum::class);
	}

	public function tagAll(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.photo.tag', [Params::PHOTO_IDS => $this->params[Params::PHOTO_IDS], Params::ALBUM_ID => $this->params[Params::ALBUM_ID]]);
	}

	public function renameAll(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.photo.rename', [Params::PHOTO_IDS => $this->params[Params::PHOTO_IDS], Params::ALBUM_ID => $this->params[Params::ALBUM_ID]]);
	}

	public function copyAllTo(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.photo.copy-to', [Params::PHOTO_IDS => $this->params[Params::PHOTO_IDS], Params::ALBUM_ID => $this->params[Params::ALBUM_ID]]);
	}

	public function moveAll(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.photo.move', [Params::PHOTO_IDS => $this->params[Params::PHOTO_IDS], Params::ALBUM_ID => $this->params[Params::ALBUM_ID]]);
	}

	public function deleteAll(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.photo.delete', [Params::PHOTO_IDS => $this->params[Params::PHOTO_IDS], Params::ALBUM_ID => $this->params[Params::ALBUM_ID]]);
	}

	public function downloadAll(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.photo.download', [Params::PHOTO_IDS => $this->params[Params::PHOTO_IDS], Params::ALBUM_ID => $this->params[Params::ALBUM_ID]]);
	}
}
