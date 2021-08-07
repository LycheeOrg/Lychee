<?php

namespace App\Http\Livewire;

use App\Factories\AlbumFactory;
use App\Models\Album as AlbumModel;
use App\Models\Configs;
use Livewire\Component;

class Album extends Component
{
	const FLKR = 'flkr';
	const MASONRY = 'masonry';
	const SQUARE = 'square';

	public string $layout = Album::MASONRY;
	public int $albumId;
	public AlbumModel $album;
	/**
	 * @var array (for now)
	 */
	public array $info;
	/**
	 * @var array (for now)
	 */
	public array $photos;

	private AlbumFactory $albumFactory;

	public function mount(AlbumModel $album, AlbumFactory $albumFactory)
	{
		$this->album = $album;
		$this->info = [];
		$this->info['albums'] = [];

		$this->albumFactory = $albumFactory;
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