<?php

namespace App\Http\Livewire\Modules;

use App\Actions\Albums\Top;
use App\Contracts\InternalLycheeException;
use App\DTO\TopAlbums;
use Illuminate\Contracts\Container\BindingResolutionException;
use Livewire\Component;

class Albums extends Component
{
	public TopAlbums $topAlbums;

	/**
	 * Initialize component.
	 *
	 * @param Top $top
	 *
	 * @throws InternalLycheeException
	 */
	public function mount(
		Top $top,
	) {
		$this->topAlbums = $top->get();
	}

	/**
	 * Render component.
	 *
	 * @throws BindingResolutionException
	 */
	public function render()
	{
		return view('livewire.pages.modules.albums');
	}
}
