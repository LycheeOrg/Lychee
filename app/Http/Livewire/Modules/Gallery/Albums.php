<?php

namespace App\Http\Livewire\Modules\Gallery;

use App\Actions\Albums\Top;
use App\Http\Livewire\Traits\InteractWithModal;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

/**
 * This is the "start" page of the gallery
 * Integrate the list of all albums at top level.
 */
class Albums extends Component
{
	use InteractWithModal;

	/** @var Collection<Album> Collection of the album owned by the user */
	public Collection $albums;

	/** @var Collection<BaseSmartAlbum> Collection of the smart album owned by the user */
	public Collection $smartalbums;

	/** @var Collection<Album> Collection of the album shared to the user */
	public Collection $shared_albums;

	/** @var string[] allows to reload and refresh the page. */
	protected $listeners = ['reload'];

	/**
	 * Render component.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		$this->reload();

		return view('livewire.modules.gallery.albums');
	}

	/**
	 * Allows queries to reload the albums list.
	 *
	 * @return void
	 */
	public function reload()
	{
		$topAlbums = resolve(Top::class)->get();
		$this->albums = $topAlbums->albums;
		$this->smartalbums = $topAlbums->smart_albums->concat($topAlbums->tag_albums)->reject(fn ($album) => $album === null);
		$this->shared_albums = $topAlbums->shared_albums;
	}

	/**
	 * When no albums are present we simply open the login modal.
	 *
	 * @return void
	 */
	public function openLoginModal(): void
	{
		$this->openModal('forms.login');
	}
}
