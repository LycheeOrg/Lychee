<?php

namespace App\Http\Livewire;

use App\Contracts\AbstractAlbum;
use App\Models\Configs;
use Livewire\Component;

class Album extends Component
{
	public const FLKR = 'flkr';
	public const MASONRY = 'masonry';
	public const SQUARE = 'square';

	public string $layout = Album::MASONRY;
	public int $albumId;
	public AbstractAlbum $album;
	/**
	 * @var array (for now)
	 */
	public array $info;
	/**
	 * @var array (for now)
	 */
	public array $photos = [];

	public function mount(AbstractAlbum $album)
	{
		$this->album = $album;
		// $this->info = [];
		// $this->info['albums'] = [];
	}

	public function render()
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

		$this->info = $this->album->toArray();

		return view('livewire.album');
	}
}