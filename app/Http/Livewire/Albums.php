<?php

namespace App\Http\Livewire;

use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\AlbumsFunctions;
use App\ModelFunctions\SessionFunctions;
use App\Models\Configs;
use Livewire\Component;

class Albums extends Component
{
	public $albums;
	public $smartalbums;
	public $shared_albums;

	/**
	 * @var AlbumFunctions
	 */
	private $albumFunctions;

	/**
	 * @var AlbumsFunctions
	 */
	private $albumsFunctions;

	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	/**
	 * @param AlbumFunctions   $albumFunctions
	 * @param AlbumsFunctions  $albumsFunctions
	 * @param SessionFunctions $sessionFunctions
	 */
	public function mount(
		AlbumFunctions $albumFunctions,
		AlbumsFunctions $albumsFunctions,
		SessionFunctions $sessionFunctions
	) {
		$this->albumFunctions = $albumFunctions;
		$this->albumsFunctions = $albumsFunctions;
		$this->sessionFunctions = $sessionFunctions;

		// caching to avoid further request
		Configs::get();

		// $toplevel containts Collection[Album] accessible at the root: albums shared_albums.
		//
		$toplevel = $this->albumsFunctions->getToplevelAlbums();
		$children = $this->albumsFunctions->get_children($toplevel);

		$this->albums = $this->albumsFunctions->prepare_albums($toplevel['albums'], $children['albums']);
		$this->shared_albums = $this->albumsFunctions->prepare_albums($toplevel['shared_albums'], $children['shared_albums']);
		$this->smartalbums = $this->albumsFunctions->getSmartAlbums($toplevel, $children);
	}

	public function render()
	{
		return view('livewire.albums');
	}
}
