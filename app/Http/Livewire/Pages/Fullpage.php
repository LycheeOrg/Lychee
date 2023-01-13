<?php

namespace App\Http\Livewire\Pages;

use App\Enum\PageMode;
use App\Factories\AlbumFactory;
use App\Http\Controllers\IndexController;
use App\Http\Livewire\Traits\AlbumProperty;
use App\Http\Livewire\Traits\InteractWithModal;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\Photo;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Redirector;

class Fullpage extends Component
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
	public ?BaseAlbum $baseAlbum = null;
	public ?BaseSmartAlbum $smartAlbum = null;


	public string $albumId = '';
	public string $photoId = '';

	public PageMode $mode;
	public string $title;
	public ?Photo $photo = null;

	/**
	 * Defines the query strings to be updated
	 *
	 * @var array<int,string>
	 */
	protected $queryString = ['albumId' => ['except'=>''], 'photoId' => ['except'=>'']];
    // protected $updatesQueryString = ['albumId', 'photoId'];

	/**
	 * @var @array<int,string> listeners of click events
	 */
	protected $listeners = ['openAlbum', 'openPhoto', 'back', 'reloadPage'];

	public function render(): View
	{
		$albumFactory = resolve(AlbumFactory::class);
		if ($this->albumId === '') {
			$this->mode = PageMode::ALBUMS;
			$this->title = Configs::getValueAsString('site_title');
		} else {
			$this->mode = PageMode::ALBUM;
			$album = $albumFactory->findAbstractAlbumOrFail($this->albumId);
			$this->loadAlbum($album);
			$this->title = $album->title;

			if ($this->photoId !== '') {
				$this->mode = PageMode::PHOTO;
				/** @var Photo $photoItem */
				$photoItem = Photo::with('album')->findOrFail($this->photoId);
				$this->photo = $photoItem;
				$this->title = $this->photo->title;
			}
		}

		$siteTitle = Configs::getValueAsString('site_title');
		$album = $this->getAlbumProperty();

		if ($this->photo !== null) {
			$description = $this->photo->description;
			$imageUrl = url()->to($this->photo->size_variants->getMedium()?->url ?? $this->photo->size_variants->getOriginal()->url);
		}
		else if ($album instanceof BaseAlbum) {
			$description = $album->description;
			$imageUrl = url()->to($album->thumb->thumbUrl ?? '');
		}
		else if ($album instanceof BaseSmartAlbum) {
			$description = '';
			$imageUrl = url()->to($album->thumb->thumbUrl ?? '');
		}
		else
		{
			$description = '';
			$imageUrl = '';
		}

		return view('livewire.pages.fullpage',)->layout('layouts.livewire',[
			'pageTitle' => $siteTitle . (!blank($siteTitle) && !blank($this->title) ? ' – ' : '') . $this->title,
			'pageDescription' => !blank($description) ? $description . ' – via Lychee' : '',
			'siteOwner' => Configs::getValueAsString('site_owner'),
			'imageUrl' => $imageUrl ?? '',
			'pageUrl' => url()->current(),
			'rssEnable' => Configs::getValueAsBool('rss_enable'),
			'bodyHtml' => file_get_contents(public_path('dist/frontend.html')),
			'userCssUrl' => IndexController::getUserCss(),
			'frame' => ''
		])->slot('fullpage');
	}

	/*
	 *  Interactions
	 */
	public function reloadPage()
	{
		return $this->render();
	}

	public function openAlbum(string $albumId)
	{
		$this->albumId = $albumId;
		$this->emit('urlChange', route('livewire_index', ['albumId' => $this->albumId]));
		return $this->render();
	}

	public function openPhoto(string $photoId)
	{
		$this->albumId = $this->getAlbumProperty()->id;
		$this->photoId = $photoId;
		$this->emit('urlChange', route('livewire_index', ['albumId' => $this->albumId, 'photoId' => $this->photoId]));
		return $this->render();
	}

	// Ideal we would like to avoid the redirect as they are slow.
	public function back()
	{
		if ($this->photo !== null) {
			$this->albumId = $this->getAlbumProperty()?->id;
			$this->photoId = '';
			$this->emit('urlChange', route('livewire_index', ['albumId' => $this->albumId]));
			return $this->render();
		}
		if ($this->baseAlbum !== null) {
			if ($this->baseAlbum instanceof Album && $this->baseAlbum->parent_id !== null) {
				$this->albumId = $this->baseAlbum->parent_id;
				$this->emit('urlChange', route('livewire_index', ['albumId' => $this->albumId]));
				return $this->render();
			}

			$this->albumId = '';
			$this->emit('urlChange', route('livewire_index'));
			return $this->render();
		}
		$this->albumId = '';
		$this->emit('urlChange', route('livewire_index'));
		return $this->render();
	}

	/**
	 * Open a login modal box.
	 *
	 * @return void
	 */
	public function openLoginModal(): void
	{
		$this->openModal('forms.login');
	}

	public function openLeftMenu(): void
	{
		$this->emitTo('components.left-menu', 'open');
	}

	public function toggleSideBar(): void
	{
		$this->emitTo('components.sidebar', 'toggle');
	}

}
