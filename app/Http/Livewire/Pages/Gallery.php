<?php

namespace App\Http\Livewire\Pages;

use App\Enum\Livewire\GalleryMode;
use App\Enum\Livewire\PageMode;
use App\Factories\AlbumFactory;
use App\Http\Livewire\Traits\AlbumProperty;
use App\Http\Livewire\Traits\InteractWithModal;
use App\Http\Livewire\Traits\UrlChange;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\Photo;
use App\SmartAlbums\BaseSmartAlbum;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\View\View;
use Livewire\Component;

class Gallery extends Component
{
	/*
	* Add interaction with modal
	*/
	use InteractWithModal;
	use UrlChange;

	/**
	 * Because AbstractAlbum is an Interface, it is not possible to make it
	 * and attribute of a Livewire Component as on the "way back" we do not know
	 * in what kind of AbstractAlbum we need to cast it back.
	 *
	 * One way to solve this would actually be to create either an WireableAlbum container
	 * Or to use a computed property on the model. We chose the later.
	 */
	use AlbumProperty;
	public ?string $albumId = null;
	public ?string $photoId = null;

	public GalleryMode $mode;
	public bool $isSidebarOpen = false;
	public string $title;

	/**
	 * Those two parameters are bound to the URL query.
	 */
	public ?BaseAlbum $baseAlbum = null;
	public ?BaseSmartAlbum $smartAlbum = null;

	public ?Photo $photo = null;

	/**
	 * @var array<int,string> listeners of click events
	 */
	protected $listeners = ['openAlbum', 'openPhoto', 'back', 'toggleSideBar'];

	/**
	 * While in most Laravel Controller calls we use the constructor,
	 * in the case of Livewire this is not possible.
	 * For this reason the method mount() is doing a similar job.
	 *
	 * @param string $albumId album parameter extracted from the route.
	 *                        The value is defaulted to '' in the case where it is not provided, e.g. in albums view.
	 * @param string $photoId photo parameter extracted from the route.
	 *                        The value is defaulted to '' in the case where it is not provided, e.g. in an album view.
	 *
	 * @return void
	 */
	public function mount(?string $albumId, ?string $photoId)
	{
		$this->albumId = $albumId;
		$this->photoId = $photoId;
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		// reload the necessary parts.
		$this->load();

		return view('livewire.pages.gallery');
	}

	/**
	 * Load data from albumId and photoId.
	 *
	 * @return void
	 */
	private function load(): void
	{
		$albumFactory = resolve(AlbumFactory::class);
		if ($this->albumId === null || $this->albumId === '') {
			$this->mode = GalleryMode::ALBUMS;
			$this->title = Configs::getValueAsString('site_title');

			$this->resetAlbums();
			$this->photo = null;

			return;
		}

		// Reload if necessary
		if ($this->getAlbumProperty()?->id !== $this->albumId) {
			// this means that $this->albumId exists
			$album = $albumFactory->findAbstractAlbumOrFail($this->albumId);
			$this->loadAlbum($album);
		}

		// We are in album view.
		if ($this->photoId === null || $this->photoId === '') {
			$this->mode = GalleryMode::ALBUM;
			$this->title = $this->getAlbumProperty()->title;
			$this->photo = null;

			return;
		}

		// Set photo Mode
		$this->mode = GalleryMode::PHOTO;
		/** @var Photo $photoItem */
		$photoItem = Photo::with('album')->findOrFail($this->photoId);
		$this->photo = $photoItem;
		$this->title = $this->photo->title;
	}

	/**
	 * Method call to open an album from either entry point or a sub album.
	 * This will unfortunately trigger a significant rendering of the front-end because
	 * of the html change.
	 * In the case of album -> subalbum only the inner part will be re-rendered (in theory).
	 *
	 * @param string $albumId
	 *
	 * @return void
	 */
	public function openAlbum(string $albumId): void
	{
		$this->albumId = $albumId;
		$this->load();
		$this->emitUrlChange(PageMode::GALLERY, $this->albumId, $this->photoId ?? '');
	}

	/**
	 * Method call to open a photo from an album.
	 * This will unfortunately trigger a significant rendering of the front-end because
	 * of the html change.
	 *
	 * @param string $photoId
	 *
	 * @return void
	 */
	public function openPhoto(string $photoId): void
	{
		$this->photoId = $photoId;
		// This ensures that the history has been updated
		$this->emitUrlChange(PageMode::GALLERY, $this->albumId, $this->photoId);
	}

	/**
	 * Method call to go back one step.
	 *
	 * @return void
	 */
	public function back(): void
	{
		if ($this->photoId !== null && $this->photoId !== '') {
			$this->photoId = null;
		} elseif ($this->baseAlbum instanceof Album && $this->baseAlbum->parent_id !== null) {
			// Case of sub-albums
			$this->albumId = $this->baseAlbum->parent_id;
		} else {
			$this->albumId = null;
		}

		if ($this->albumId === null) {
			// In the case of smartAlbums or full gallery, we just close
			$this->emitTo('components.sidebar', 'close');
		}
		$this->emitTo('components.sidebar', 'resetPhoto');

		// This ensures that the history has been updated
		$this->emitUrlChange(PageMode::GALLERY, $this->albumId ?? '', $this->photoId ?? '');
	}

	/**
	 * Toggle the component.
	 *
	 * @return void
	 */
	public function toggleSideBar(): void
	{
		Debugbar::info('toggle.');
		$this->isSidebarOpen = !$this->isSidebarOpen;
	}
}
