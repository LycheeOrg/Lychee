<?php

namespace App\Http\Livewire;

use App\Enum\Livewire\PageMode;
use App\Factories\AlbumFactory;
use App\Http\Controllers\IndexController;
use App\Http\Livewire\Traits\UrlChange;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\Photo;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Defines the Index page.
 * Here all starts.
 */
class Index extends Component
{
	use UrlChange;

	public PageMode $page_mode;
	public ?string $albumId = null;
	public ?string $photoId = null;

	/**
	 * @var string[] listeners of events
	 */
	protected $listeners = [
		'openPage',
		'reloadPage',
		'back',
	];

	/**
	 * Mounting point of the index.
	 *
	 * @param string|null $page
	 * @param string|null $albumId
	 * @param string|null $photoId
	 *
	 * @return void|\Illuminate\Http\RedirectResponse
	 */
	public function mount(?string $page = null, ?string $albumId = null, ?string $photoId = null)
	{
		$default_page = Configs::getValueAsBool('landing_page_enable') ? PageMode::LANDING : PageMode::GALLERY;

		if ($page === 'landing' && !Configs::getValueAsBool('landing_page_enable')) {
			return redirect()->route('livewire_index');
		}

		$this->page_mode = PageMode::tryFrom($page) ?? $default_page;
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
		/** @phpstan-ignore-next-line */
		return view('livewire.index')->layout('layouts.livewire', $this->getLayout())->slot('fullpage');
	}

	/**
	 * Fetch layout data for the head, meta etc.
	 *
	 * @return array
	 */
	private function getLayout(): array
	{
		$siteTitle = Configs::getValueAsString('site_title');
		$title = '';
		$description = '';
		$imageUrl = '';

		if ($this->photoId !== null) {
			$photo = Photo::findOrFail($this->photoId);
			$title = $photo->title;
			$description = $photo->description;
			$imageUrl = url()->to($photo->size_variants->getMedium()?->url ?? $photo->size_variants->getOriginal()->url);
		} elseif ($this->albumId !== null) {
			$albumFactory = resolve(AlbumFactory::class);
			$album = $albumFactory->findAbstractAlbumOrFail($this->albumId, false);
			$title = $album->title;
			$description = $album instanceof BaseAlbum ? $album->description : '';
			$imageUrl = url()->to($album->thumb->thumbUrl ?? '');
		}

		return [
			'pageTitle' => $siteTitle . (!blank($siteTitle) && !blank($title) ? ' – ' : '') . $title,
			'pageDescription' => !blank($description) ? $description . ' – via Lychee' : '',
			'siteOwner' => Configs::getValueAsString('site_owner'),
			'imageUrl' => $imageUrl,
			'pageUrl' => url()->current(),
			'rssEnable' => Configs::getValueAsBool('rss_enable'),
			'userCssUrl' => IndexController::getUserCustomFiles('user.css'),
			'userJsUrl' => IndexController::getUserCustomFiles('custom.js'),
			'frame' => '',
		];
	}

	/**
	 * Open page.
	 *
	 * @return void
	 */
	public function openPage(string $page): void
	{
		$this->albumId = null;
		$this->photoId = null;
		$this->page_mode = PageMode::from($page);

		// update URL
		$this->emitUrlChange($this->page_mode, $this->albumId ?? '', $this->photoId ?? '');
	}

	/*
	 ** This triggers a full reloading of the page
	 */
	public function reloadPage(): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
	{
		return redirect(route('livewire_index', ['page' => $this->page_mode, 'albumId' => $this->albumId, 'photoId' => $this->photoId]));
	}

	/**
	 * Method call to go back one step.
	 */
	public function back(): void
	{
		$this->albumId = null;
		$this->photoId = null;
		$this->page_mode = PageMode::GALLERY;

		// This ensures that the history has been updated
		$this->emitUrlChange(PageMode::GALLERY, '', '');
	}
}
