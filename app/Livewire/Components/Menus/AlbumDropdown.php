<?php

namespace App\Livewire\Components\Menus;

use App\Contracts\Livewire\Params;
use App\Livewire\Components\Pages\Gallery\Album;
use App\Livewire\Traits\InteractWithContextMenu;
use App\Livewire\Traits\InteractWithModal;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * This defines the context menu when right clicking on a single albums.
 */
class AlbumDropdown extends Component
{
	use InteractWithModal;
	use InteractWithContextMenu;

	/** @var array{parentID:?string,albumID:string} */
	#[Locked] public array $params;
	/**
	 * Renders the Add menu in the top right.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.context-menus.album-dropdown');
	}

	public function rename(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.album.rename', [Params::ALBUM_ID => $this->params[Params::ALBUM_ID], Params::PARENT_ID => $this->params[Params::PARENT_ID]]);
	}

	public function merge(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.album.merge', [Params::ALBUM_ID => $this->params[Params::ALBUM_ID], Params::PARENT_ID => $this->params[Params::PARENT_ID]]);
	}

	public function move(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.album.move', [Params::ALBUM_ID => $this->params[Params::ALBUM_ID], Params::PARENT_ID => $this->params[Params::PARENT_ID]]);
	}

	public function delete(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.album.delete', [Params::ALBUM_ID => $this->params[Params::ALBUM_ID], Params::PARENT_ID => $this->params[Params::PARENT_ID]]);
	}

	public function download(): void
	{
		$this->closeContextMenu();
		$this->redirect(route('download', ['albumIDs' => $this->params[Params::ALBUM_ID]]));
	}

	public function setAsCover(): void
	{
		$this->closeContextMenu();
		$this->dispatch('setAsCover', $this->params[Params::ALBUM_ID])->to(Album::class);
	}
}
