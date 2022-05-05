<?php

namespace App\Http\Livewire\Modules;

use App\Actions\Albums\Top;
use App\Contracts\InternalLycheeException;
use App\DTO\TopAlbums;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Livewire\Component;

class Albums extends Component
{
	private TopAlbums $topAlbums;

	public Collection $albums;
	public Collection $smartalbums;
	public Collection $shared_albums;

	/**
	 * Initialize component.
	 *
	 * @param Top $top
	 *
	 * @throws InternalLycheeException
	 */
	public function mount(Top $top)
	{
		$this->topAlbums = $top->get();
	}

	/**
	 * Render component.
	 *
	 * @throws BindingResolutionException
	 */
	public function render()
	{
		$this->albums = $this->topAlbums->albums;
		$this->smartalbums = $this->topAlbums->smartAlbums->concat($this->topAlbums->tagAlbums)->reject(fn ($album) => $album == null);
		$this->shared_albums = $this->topAlbums->sharedAlbums;

		return view('livewire.pages.modules.albums');
	}
}
