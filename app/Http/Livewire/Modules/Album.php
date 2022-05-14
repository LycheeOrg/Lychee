<?php

namespace App\Http\Livewire\Modules;

use App\Contracts\AbstractAlbum;
use App\Models\Configs;
use Illuminate\View\View;
use Livewire\Component;

class Album extends Component
{
	public const FLKR = 'flkr';
	public const MASONRY = 'masonry';
	public const SQUARE = 'square';

	public string $layout = Album::MASONRY;
	public AbstractAlbum $album;

	public function render(): View
	{
		switch (Configs::get_value('layout')) {
			case '0':
				$this->layout = Album::SQUARE;
				break;
			case '1':
				$this->layout = Album::FLKR;
				break;
			case '2':
				$this->layout = Album::MASONRY;
				break;
			default:
				$this->layout = Album::FLKR;
		}

		return view('livewire.pages.modules.album');
	}
}
