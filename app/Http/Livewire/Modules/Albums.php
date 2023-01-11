<?php

namespace App\Http\Livewire\Modules;

use App\Actions\Albums\Top;
use App\Contracts\Exceptions\InternalLycheeException;
use App\DTO\TopAlbums;
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
	/** @var TopAlbums This is just here for the computations before the rendering. */
	private TopAlbums $topAlbums;

	/** @var Collection<Album> Collection of the album owned by the user */
	public Collection $albums;

	/** @var Collection<BaseSmartAlbum> Collection of the smart album owned by the user */
	public Collection $smartalbums;

	/** @var Collection<Album> Collection of the album shared to the user */
	public Collection $shared_albums;

	/**
	 * Initialize component.
	 *
	 * @param Top $top this is injected by DDI
	 *
	 * @throws InternalLycheeException
	 */
	public function mount(Top $top): void
	{
		$this->topAlbums = $top->get();
	}

	/**
	 * Render component.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		$this->albums = $this->topAlbums->albums;
		$this->smartalbums = $this->topAlbums->smart_albums->concat($this->topAlbums->tag_albums)->reject(fn ($album) => $album === null);
		$this->shared_albums = $this->topAlbums->shared_albums;

		return view('livewire.pages.modules.albums');
	}
}
