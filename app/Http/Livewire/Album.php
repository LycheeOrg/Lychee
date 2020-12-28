<?php

namespace App\Http\Livewire;

use App\Factories\AlbumFactory;
use App\ModelFunctions\AlbumFunctions;
use App\Models\Configs;
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
	 * @var AlbumFunctions
	 */
	private $albumFunctions;

	public function mount($albumId, AlbumFactory $albumFactory, AlbumFunctions $albumFunctions)
	{
		$this->albumId = $albumId;
		$this->album = null;
		$this->info = [];
		$this->info['albums'] = [];

		$this->albumFactory = $albumFactory;
		$this->albumFunctions = $albumFunctions;
	}

	public function render()
	{
		$this->album = $this->albumFactory->make($this->albumId);

		if ($this->album->is_smart()) {
			$publicAlbums = $this->albumsFunctions->getPublicAlbumsId();
			$this->album->setAlbumIDs($publicAlbums);
			$this->info = $this->album->toArray();
		} else {
			// take care of sub albums
			$children = $this->album->get_children();

			$return = $this->album->toArray();
			$this->info['albums'] = $children->map(function ($child) {
				$arr_child = $child->toArray();
				$thb = $child->get_thumbs();
				$child->set_thumbs($arr_child, $thb);

				return $arr_child;
			})->values();
			$this->info['owner'] = $this->album->owner->name();
		}

		// take care of photos
		$full_photo = $this->info['full_photo'] ?? Configs::get_value('full_photo', '1') === '1';
		$photos_query = $this->album->get_photos();
		$this->photos = $this->albumFunctions->photos($this->album, $photos_query, $full_photo, $this->album->get_license());

		$this->info['id'] = $this->albumId;
		$this->info['num'] = strval(count($this->photos));

		return view('livewire.album');
	}
}
