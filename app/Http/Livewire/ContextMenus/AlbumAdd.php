<?php

namespace App\Http\Livewire\ContextMenus;

use App\Http\Livewire\Traits\InteractWithContextMenu;
use App\Http\Livewire\Traits\InteractWithModal;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * This defines the context menu when clicking in the header to add an album.
 */
class AlbumAdd extends Component
{
	use InteractWithModal;
	use InteractWithContextMenu;
	public array $params;

	/**
	 * Renders the Add menu in the top right.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.context-menus.album-add');
	}

	/**
	 * Open Create Album modal.
	 *
	 * @return void
	 */
	public function openAlbumCreateModal(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.album.create', $this->params);
	}

	/**
	 * Open Create Tag Album modal.
	 *
	 * @return void
	 */
	public function openTagAlbumCreateModal(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.album.create-tag', $this->params);
	}

	public function openImportFromServerModal(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.import-from-server');
	}

	public function openImportFromUrlModal(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.import.import-from-url', $this->params);
	}

	public function openUploadModal(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.upload', $this->params);
	}
}
