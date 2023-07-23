<?php

namespace App\Livewire\Pages;

use App\Enum\Livewire\GalleryMode;
use App\Enum\Livewire\PageMode;
use App\Factories\AlbumFactory;
use App\Livewire\Traits\AlbumProperty;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\Photo;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Gallery extends Component
{
	/*
	* Add interaction with modal
	*/
	use InteractWithModal;

	/**
	 * Because AbstractAlbum is an Interface, it is not possible to make it
	 * and attribute of a Livewire Component as on the "way back" we do not know
	 * in what kind of AbstractAlbum we need to cast it back.
	 *
	 * One way to solve this would actually be to create either an WireableAlbum container
	 * Or to use a computed property on the model. We chose the later.
	 */
	use AlbumProperty;

	private AlbumFactory $albumFactory;

	#[Locked]
	public ?string $albumId = null;
	#[Locked]
	public ?string $photoId = null;
	#[Locked]
	public GalleryMode $mode;
	#[Locked]
	public string $title;

	/**
	 * Those two parameters are bound to the URL query.
	 */
	#[Locked]
	public ?BaseAlbum $baseAlbum = null;
	#[Locked]
	public ?BaseSmartAlbum $smartAlbum = null;
	#[Locked]
	public ?Photo $photo = null;

	/**
	 * @var array<int,string> listeners of click events
	 */
	protected $listeners = ['openAlbum', 'openPhoto', 'back'];

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
	public function mount(?string $albumId = null, ?string $photoId = null)
	{
		$this->albumId = $albumId;
		$this->photoId = $photoId;
	}

	public function boot()
	{
		$this->albumFactory = resolve(AlbumFactory::class);
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
			$album = $this->albumFactory->findAbstractAlbumOrFail($this->albumId, false);
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
	// public function openAlbum(string $albumId): void
	// {
	// 	$this->albumId = $albumId;
	// 	$this->load();
	// 	// $this->emitUrlChange(PageMode::GALLERY, $this->albumId, $this->photoId ?? '');
	// }

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
		// $this->emitUrlChange(PageMode::GALLERY, $this->albumId, $this->photoId);
	}

	/**
	 * Method call to go back one step.
	 */
	public function back()
	{
		if ($this->photoId !== null && $this->photoId !== '') {
			return $this->redirect(route('livewire-gallery', ['albumId' => $this->albumId]));
		}
		if ($this->baseAlbum instanceof Album && $this->baseAlbum->parent_id !== null) {
			return $this->redirect(route('livewire-gallery', ['albumId' => $this->baseAlbum->parent_id]));
		}

		return $this->redirect(route('livewire-gallery'));
	}
}
