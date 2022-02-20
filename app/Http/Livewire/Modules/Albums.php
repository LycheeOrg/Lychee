<?php

namespace App\Http\Livewire\Modules;

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

		$toplevel = $this->top->get();

		$this->albums = $toplevel['albums'];
		$this->shared_albums = $toplevel['shared_albums'];
		$this->smartalbums = $this->smart->get();
	}

	/**
	 * Render component.
	 */
	public function render()
	{
		return view('livewire.pages.modules.albums');
	}
}
