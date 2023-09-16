<?php

namespace App\Livewire\Components\Menus;

use App\Contracts\Livewire\Params;
use App\Livewire\Traits\InteractWithContextMenu;
use App\Livewire\Traits\InteractWithModal;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * This defines the context menu when right clicking on a single photo.
 */
class PhotoDropdown extends Component
{
	use InteractWithModal;
	use InteractWithContextMenu;

	/** @var array{albumID:?string,photoID:string} */
	#[Locked] public array $params;
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
		// $this->openModal('forms.add.upload', $this->params);
	}

	public function tag(): void
	{
		$this->closeContextMenu();
		// $this->openModal('forms.add.upload', $this->params);
	}

	public function setAsCover(): void
	{
		$this->closeContextMenu();
		// $this->openModal('forms.add.upload', $this->params);
	}

	public function rename(): void
	{
		$this->closeContextMenu();
		// $this->openModal('forms.add.upload', $this->params);
	}

	public function copyTo(): void
	{
		$this->closeContextMenu();
		// $this->openModal('forms.add.upload', $this->params);
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
