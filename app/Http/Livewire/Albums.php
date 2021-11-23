<?php

namespace App\Http\Livewire;

use App\Actions\Albums\Prepare;
use App\Actions\Albums\Smart;
use App\Actions\Albums\Top;
use Livewire\Component;

class Albums extends Component
{
	public $albums;
	public $smartalbums;
	public $shared_albums;

	/** @var Prepare */
	private $prepareAlbum;

	/** @var Top */
	private $top;

	/** @var Smart */
	private $smart;

	/**
	 * Initialize component.
	 *
	 * @param AlbumsFunctions $albumsFunctions
	 * @param Top             $top
	 * @param Smart           $smart
	 */
	public function mount(
		Prepare $prepareAlbum,
		Top $top,
		Smart $smart
	) {
		$this->prepareAlbum = $prepareAlbum;
		$this->top = $top;
		$this->smart = $smart;

		// $toplevel containts Collection<Album> accessible at the root: albums shared_albums.
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
