<?php

namespace App\Http\Livewire\Components;

use App\Enum\Livewire\GalleryMode;
use App\Enum\Livewire\PageMode;
use App\Http\Livewire\Traits\AlbumProperty;
use App\Http\Livewire\Traits\InteractWithModal;
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

	public ?PageMode $page_mode = null;
	public ?GalleryMode $gallery_mode = null;
	public string $title = '';
	public bool $is_hidden = false;

	public ?BaseAlbum $baseAlbum = null;
	public ?BaseSmartAlbum $smartAlbum = null;
	public ?Photo $photo = null;

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
		$this->openModal('forms.login');
	}

	/**
	 * Go back one step.
	 * TODO Consider moving this directly to Blade.
	 *
	 * @return void
	 */
	public function back(): void
	{
		if ($this->page_mode === PageMode::GALLERY) {
			$this->emitTo('pages.gallery', 'back');
		} else {
			$this->emitTo('index', 'back');
		}
	}

	/**
	 * Open the Left menu.
	 * TODO Consider moving this directly to Blade.
	 *
	 * @return void
	 */
	public function openLeftMenu(): void
	{
		$this->emitTo('components.left-menu', 'open');
	}

	/**
	 * Toggle the side bar.
	 * TODO Consider moving this directly to Blade.
	 *
	 * @return void
	 */
	public function toggleSideBar(): void
	{
		$this->emitTo('pages.gallery', 'toggleSideBar');
	}

	/**
	 * Open the context menu in the top right corner.
	 *
	 * @return void
	 */
	public function openContextMenu(): void
	{
		$this->emitTo('components.base.context-menu', 'openContextMenu', 'album-add', ['parentId' => $this->baseAlbum?->id]);
	}
}