<?php

namespace App\Http\Livewire;

use App\Actions\Album\Photos;
use App\Factories\AlbumFactory;
use Livewire\Component;
use PublicIds;

class Album extends Component
{
	/**
	 * @var int
	 */
	public $albumId;

	/**
	 * @var Album
	 */
	public $album;

	/**
	 * @var array (for now)
	 */
	public $info;

	/**
	 * @var array (for now)
	 */
	public $photos;

	/**
	 * @var AlbumFactory
	 */
	private $albumFactory;

	private $photosAction;

	public function mount($albumId, AlbumFactory $albumFactory, Photos $photosAction)
	{
		$this->albumId = $albumId;
		$this->album = null;
		$this->info = [];
		$this->info['albums'] = [];

		$this->albumFactory = $albumFactory;
		$this->photosAction = $photosAction;
	}

	public function render()
	{
		$this->album = $this->albumFactory->make($this->albumId);

		if ($this->album->smart) {
			$publicAlbums = resolve(PublicIds::class)->getPublicAlbumsId();
			$this->album->setAlbumIDs($publicAlbums);
		}
		$this->info = $this->album->toReturnArray();

		// take care of sub albums
		$this->info['albums'] = $this->album->get_children()->map(fn (Album $a) => $a->toReturnArray())->values();

		// take care of photos
		$this->photos = $this->photosAction->get($this->album);

		$this->info['id'] = $this->albumId;
		$this->info['num'] = strval(count($this->photos));

		return view('livewire.album');
	}
}
