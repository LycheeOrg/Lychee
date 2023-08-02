<?php

namespace App\Livewire\Components\Pages\Gallery;

use App\Contracts\Models\AbstractAlbum;
use App\Factories\AlbumFactory;
use App\Models\Extensions\BaseAlbum;
use App\Models\Photo as PhotoModel;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Similar to the Album module, this takes care of displaying a single photo.
 */
class Photo extends Component
{
	private AlbumFactory $albumFactory;

	#[Locked]
	public string $albumId;

	#[Locked]
	public string $photoId;

	public bool $autoplay = true;

	/** @var PhotoModel Said photo to be displayed */
	public PhotoModel $photo;
	public ?AbstractAlbum $album = null;
	public bool $is_base_album = false;

	// ! Will be used later
	public bool $visibleControls = false;

	public function boot(): void
	{
		$this->albumFactory = resolve(AlbumFactory::class);
	}

	public function mount(string $albumId, string $photoId): void
	{
		$this->albumId = $albumId;
		$this->photoId = $photoId;
		$this->album = $this->albumFactory->findAbstractAlbumOrFail($this->albumId);
		$this->is_base_album = $this->album instanceof BaseAlbum;
		/** @var PhotoModel $photoItem */
		$photoItem = PhotoModel::with('album')->findOrFail($this->photoId);
		$this->photo = $photoItem;

		// $this->locked = Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * Render the associated view.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		return view('livewire.pages.gallery.photo');
	}

	public function back(): mixed
	{
		return $this->redirect(route('livewire-gallery-album', ['albumId' => $this->albumId]));
	}

	#[On('reloadPage')]
	public function reloadPage(): void
	{
	}
}
