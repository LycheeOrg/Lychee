<?php

namespace App\Http\Livewire\Modules;

use App\Contracts\Models\AbstractAlbum;
use App\Enum\Livewire\AlbumMode;
use App\Models\Configs;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Album sub module.
 *
 * We just load the layout from config and render.
 * The variable $album is automatically mounted from the Livewire call
 */
class Album extends Component
{
	public AlbumMode $layout;
	public AbstractAlbum $album;

	/**
	 * Rendering of the blade template.
	 *
	 * @return View
	 */
	public function render(): View
	{
		$this->layout = Configs::getValueAsEnum('layout', AlbumMode::class);

		return view('livewire.pages.modules.album');
	}
}
