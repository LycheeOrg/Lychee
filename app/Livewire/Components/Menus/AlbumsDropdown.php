<?php

namespace App\Livewire\Components\Menus;

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
		// $this->openModal('forms.add.upload', $this->params);
	}

	public function mergeAll(): void
	{
		$this->closeContextMenu();
		// $this->openModal('forms.add.upload', $this->params);
	}

	public function moveAll(): void
	{
		$this->closeContextMenu();
		// $this->openModal('forms.add.upload', $this->params);
	}

	public function deleteAll(): void
	{
		$this->closeContextMenu();
		// $this->openModal('forms.add.upload', $this->params);
	}

	public function donwloadAll(): void
	{
		$this->closeContextMenu();
		// $this->openModal('forms.add.upload', $this->params);
	}
}
