<?php

namespace App\Livewire\Components;

use App\Enum\Livewire\GalleryMode;
use App\Livewire\Traits\AlbumProperty;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Extensions\BaseAlbum;
use App\Models\Photo;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Header of the page.
 * Provides a wide variety of actions:
 * - login
 * - open left menu
 * - quick interaction with albums
 * - quicj interaction with photos
 * - context menu for add.
 */
class Header extends Component
{
	use InteractWithModal;
	use AlbumProperty;

	public ?GalleryMode $gallery_mode = null;
	public string $title = '';
	public bool $is_hidden = false;

	// Used to determine whether some actions are possible or not.
	public ?BaseAlbum $baseAlbum = null;
	public ?BaseSmartAlbum $smartAlbum = null;
	public ?Photo $photo = null;

	public bool $albumToggled = false;
	public bool $leftBarToggled = false;

	/** @var array<int,string> */
	protected $listeners = ['toggleAlbumDetails'];

	/**
	 * Render the header.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.components.header');
	}

	/**
	 * Open a login modal box.
	 * TODO Consider moving this directly to Blade.
	 *
	 * @return void
	 */
	public function openLoginModal(): void
	{
		$this->openModal('modals.login');
	}

	/**
	 * Open the Left menu.
	 * TODO Consider moving this directly to Blade.
	 *
	 * @return void
	 */
	public function openLeftMenu(): void
	{
		$this->dispatchTo('components.left-menu', 'open');
	}

	/**
	 * Toggle details of albums.
	 *
	 * @return void
	 */
	public function toggleAlbumDetails(): void
	{
		$this->albumToggled = !$this->albumToggled;
		$this->dispatchTo('modules.gallery.album', 'toggle');
	}

	/**
	 * Toggle details of photos.
	 *
	 * @return void
	 */
	public function togglePhotoDetails(): void
	{
		$this->dispatchTo('modules.gallery.photo', 'toggle');
	}

	/**
	 * Open the context menu in the top right corner.
	 *
	 * @return void
	 */
	public function openContextMenu(): void
	{
		$this->dispatchTo('components.base.context-menu', 'openContextMenu', 'album-add', ['parentId' => $this->baseAlbum?->id]);
	}
}