<?php

namespace App\Livewire\Components\Menus;

use App\Contracts\Livewire\Params;
use App\Livewire\Traits\InteractWithContextMenu;
use App\Livewire\Traits\InteractWithModal;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * This defines the context menu when right clicking on multiple albums.
 */
class AlbumsDropdown extends Component
{
	use InteractWithModal;
	use InteractWithContextMenu;

	/** @var array{parentID:?string,albumIDs:string[]} */
	#[Locked] public array $params;
	/**
	 * Renders the Add menu in the top right.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.context-menus.albums-dropdown');
	}

	public function renameAll(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.album.rename', [Params::ALBUM_IDS => $this->params[Params::ALBUM_IDS], Params::PARENT_ID => $this->params[Params::PARENT_ID]]);
	}

	public function mergeAll(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.album.merge', [Params::ALBUM_IDS => $this->params[Params::ALBUM_IDS], Params::PARENT_ID => $this->params[Params::PARENT_ID]]);
	}

	public function moveAll(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.album.move', [Params::ALBUM_IDS => $this->params[Params::ALBUM_IDS], Params::PARENT_ID => $this->params[Params::PARENT_ID]]);
	}

	public function deleteAll(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.album.delete', [Params::ALBUM_IDS => $this->params[Params::ALBUM_IDS], Params::PARENT_ID => $this->params[Params::PARENT_ID]]);
	}

	public function downloadAll(): void
	{
		$this->redirect(route('download') . '?albumIDs=' . implode(',', $this->params[Params::ALBUM_IDS]));
		$this->closeContextMenu();
	}
}
