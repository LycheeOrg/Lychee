<?php

namespace App\Http\Livewire;

use App\Actions\Album\Photos;
use App\Factories\AlbumFactory;
use Livewire\Component;

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

	/**
	 * @var Photos
	 */
	private $photosFunctions;

	public function mount($albumId, AlbumFactory $albumFactory, Photos $photosFunctions)
	{
		$this->albumId = $albumId;
		$this->album = null;
		$this->info = [];
		$this->info['albums'] = [];

		$this->albumFactory = $albumFactory;
		$this->photos = $photosFunctions;
	}

	public function render()
	{
		$this->album = $this->albumFactory->make($this->albumId);

		if ($this->album->is_smart()) {
			$publicAlbums = $this->albumsFunctions->getPublicAlbumsId();
			$this->album->setAlbumIDs($publicAlbums);
		} else {
			// take care of sub albums
			$this->info['albums'] = $this->album->get_children()->map(function ($child) {
				$arr_child = $child->toReturnArray();
				$child->set_thumbs($arr_child, $child->get_thumbs());

				return $arr_child;
			})->values();
		}
		$this->info = $this->album->toReturnArray();

		// take care of photos
		$this->photos = $this->photosFunctions->get($this->album);

		$this->info['id'] = $this->albumId;
		$this->info['num'] = strval(count($this->photos));

		return view('livewire.album');
	}
}
