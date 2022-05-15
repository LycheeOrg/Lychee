<?php

namespace App\Http\Livewire\Modules;

use App\Contracts\AbstractAlbum;
use App\Enum\AlbumMode;
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
		$this->layout = match (Configs::get_value('layout')) {
			'0' => AlbumMode::SQUARE(),
			'1' => AlbumMode::FLKR(),
			'2' => AlbumMode::MASONRY(),
			default => AlbumMode::FLKR()
		};

		return view('livewire.pages.modules.album');
	}
}
