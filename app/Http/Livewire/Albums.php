<?php

namespace App\Http\Livewire;

use App\Actions\Albums\Smart;
use App\Actions\Albums\Top;
use App\Contracts\InternalLycheeException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Livewire\Component;

class Albums extends Component
{
	public array $albums;
	public array $smartalbums;
	public array $shared_albums;
	private Top $top;
	private Smart $smart;

	/**
	 * Initialize component.
	 *
	 * @param Top   $top
	 * @param Smart $smart
	 *
	 * @throws InternalLycheeException
	 */
	public function mount(
		Top $top,
		Smart $smart
	) {
		$this->top = $top;
		$this->smart = $smart;

		// $toplevel contains Collection<Album> accessible at the root: albums shared_albums.
		$toplevel = $this->top->get();

		$this->albums = $toplevel['albums'];
		$this->shared_albums = $toplevel['shared_albums'];

		$this->smartalbums = $this->smart->get();
	}

	/**
	 * Render component.
	 *
	 * @throws BindingResolutionException
	 */
	public function render()
	{
		return view('livewire.albums');
	}
}
