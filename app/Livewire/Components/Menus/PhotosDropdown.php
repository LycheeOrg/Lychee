<?php

namespace App\Livewire\Components\Menus;

use App\Contracts\Livewire\Params;
use App\Livewire\Traits\InteractWithContextMenu;
use App\Livewire\Traits\InteractWithModal;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * This defines the context menu when right clicking on multiple photo.
 */
class PhotosDropDown extends Component
{
	use InteractWithModal;
	use InteractWithContextMenu;

	/** @var array{albumID:?string,photoIDs:array<int,string>} */
	#[Locked] public array $params;
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
		// $this->openModal('forms.add.upload', $this->params);
	}

	public function tagAll(): void
	{
		$this->closeContextMenu();
		// $this->openModal('forms.add.upload', $this->params);
	}

	public function renameAll(): void
	{
		$this->closeContextMenu();
		// $this->openModal('forms.add.upload', $this->params);
	}

	public function copyAllTo(): void
	{
		$this->closeContextMenu();
		// $this->openModal('forms.add.upload', $this->params);
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
