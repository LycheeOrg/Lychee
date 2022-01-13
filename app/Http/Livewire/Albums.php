<?php

namespace App\Http\Livewire;

use App\Actions\Albums\Smart;
use App\Actions\Albums\Top;
use Livewire\Component;

class Albums extends Component
{
	public $albums;
	public $smartalbums;
	public $shared_albums;

	/** @var Top */
	private $top;

	/** @var Smart */
	private $smart;

	/**
	 * Initialize component.
	 *
	 * @param Top   $top
	 * @param Smart $smart
	 */
	public function mount(
		Top $top,
		Smart $smart
	) {
		$this->top = $top;
		$this->smart = $smart;

		// $toplevel contains Collection<Album> accessible at the root: albums shared_albums.
		$toplevel = $this->top->get();

		$this->albums = $this->prepareAlbum->do($toplevel['albums']);
		$this->shared_albums = $this->prepareAlbum->do($toplevel['shared_albums']);

		$this->smartalbums = $this->smart->get();
	}

	/**
	 * Render component.
	 */
	public function render()
	{
		return view('livewire.albums');
	}
}
