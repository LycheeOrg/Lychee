<?php

namespace App\Http\Livewire;

use App\Actions\Albums\Smart;
use App\Actions\Albums\Tag;
use App\Actions\Albums\Top;
use App\ModelFunctions\AlbumsFunctions;
use Livewire\Component;

class Albums extends Component
{
	public $albums;
	public $smartalbums;
	public $shared_albums;

	/**
	 * @var AlbumsFunctions
	 */
	private $albumsFunctions;

	private $top;
	private $tag;
	private $smart;

	/**
	 * @param AlbumsFunctions $albumsFunctions
	 */
	public function mount(
		AlbumsFunctions $albumsFunctions,
		Tag $tag,
		Top $top,
		Smart $smart
	) {
		// $this->albumFunctions = $albumFunctions;
		$this->albumsFunctions = $albumsFunctions;
		$this->top = $top;
		$this->tag = $tag;
		$this->smart = $smart;

		// $toplevel containts Collection[Album] accessible at the root: albums shared_albums.
		//
		$toplevel = $this->get->top();
		$children = $this->albumsFunctions->get_children($toplevel);

		$this->albums = $this->albumsFunctions->prepare_albums($toplevel['albums'], $children['albums']);
		$this->shared_albums = $this->albumsFunctions->prepare_albums($toplevel['shared_albums'], $children['shared_albums']);
		$this->smartalbums = $this->smart->get();
	}

	public function render()
	{
		return view('livewire.albums');
	}
}
