<?php

declare(strict_types=1);

namespace App\Livewire\Components\Menus;

use App\Contracts\Livewire\Params;
use App\Factories\AlbumFactory;
use App\Livewire\Traits\InteractWithContextMenu;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Album;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * This defines the context menu when clicking in the header to add an album.
 */
class AlbumAdd extends Component
{
	use InteractWithModal;
	use InteractWithContextMenu;

	/** @var array{parentID:string|null} */
	#[Locked] public array $params;
	private ?Album $album = null;

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
		$this->openModal('forms.add.import-from-server', $this->params);
	}

	public function openImportFromUrlModal(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.add.import-from-url', $this->params);
	}

	public function openUploadModal(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.add.upload', $this->params);
	}

	public function openImportFromDropboxModal(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.add.import-from-dropbox', $this->params);
	}

	public function openAddTrackModal(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.album.add-track', $this->params);
	}

	public function openDeleteTrackModal(): void
	{
		$this->closeContextMenu();
		$this->openModal('forms.album.delete-track', $this->params);
	}

	public function getHasParentProperty(): bool
	{
		return $this->getParentAlbum() !== null;
	}

	public function getCanAddTrackProperty(): bool
	{
		return $this->getParentAlbum() !== null;
	}

	public function getHasTrackProperty(): bool
	{
		return $this->getParentAlbum()?->track_short_path !== null;
	}

	public function getParentAlbum(): Album|null
	{
		$id = $this->params[Params::PARENT_ID];
		if ($id === null) {
			return null;
		}

		if ($this->album !== null) {
			return $this->album;
		}

		$album_candidate = resolve(AlbumFactory::class)->findAbstractAlbumOrFail($id);
		if ($album_candidate instanceof Album) {
			$this->album = $album_candidate;
		}

		return $this->album;
	}
}
