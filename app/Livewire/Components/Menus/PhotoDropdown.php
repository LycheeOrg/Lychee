<?php

namespace App\Livewire\Components\Menus;

use App\Livewire\Traits\InteractWithContextMenu;
use App\Livewire\Traits\InteractWithModal;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * This defines the context menu when right clicking on a single photo.
 */
class PhotoDropDown extends Component
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
		$this->openModal('forms.photo.move', ['photoId' => $this->params['photoId'], 'albumId' => $this->params['albumId']]);
	}

	public function delete(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.photo.delete', ['photoId' => $this->params['photoId'], 'albumId' => $this->params['albumId']]);
	}

	public function donwload(): void
	{
		$this->closeContextMenu();
		dd($this->params);
		$this->openModal('forms.photo.download', ['photoId' => $this->params['photoId'], 'albumId' => $this->params['albumId']]);
		// $this->openModal('forms.add.upload', $this->params);
	}
}
