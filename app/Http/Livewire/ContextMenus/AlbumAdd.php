<?php

namespace App\Http\Livewire\ContextMenus;

use App\Http\Livewire\Traits\InteractWithContextMenu;
use App\Http\Livewire\Traits\InteractWithModal;
use Livewire\Component;

/**
 * This defines the Login Form used in modals.
 */
class AlbumAdd extends Component
{
	use InteractWithModal;
	use InteractWithContextMenu;
	public array $params;

	public function render()
	{
		return view('livewire.context-menus.album-add');
	}

	public function openAlbumCreateModal()
	{
		$this->closeContextMenu();
		$this->openModal('forms.album.create', $this->params);
	}
}
