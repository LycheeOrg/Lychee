<?php

namespace App\Http\Livewire;

use App\Actions\Album\Cast as AlbumCast;
use App\Actions\Album\Get as AlbumGet;
use App\ModelFunctions\AlbumFunctions;
use App\Models\Album as AlbumModel;
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
	 * @var AlbumGet
	 */
	private $albumGet;

	/**
	 * @var AlbumFunctions
	 */
	private $albumFunctions;

	public function mount($albumId, AlbumGet $albumGet, AlbumFunctions $albumFunctions)
	{
		$this->albumId = $albumId;
		$this->album = null;
		$this->info = [];
		$this->info['albums'] = [];

		$this->albumGet = $albumGet;
		$this->albumFunctions = $albumFunctions;
	}

	public function render()
	{
		// Get photos
		// change this for smartalbum
		/*
		 * @var AlbumModel
		 */
		$this->album = $this->albumGet->find($this->albumId);

		if ($this->album->smart) {
			$publicAlbums = $this->albumsFunctions->getPublicAlbumsId();
			$this->album->setAlbumIDs($publicAlbums);
			$this->info = AlbumCast::toArray($this->album);
		} else {
			// take care of sub albums
			$children = $this->albumFunctions->get_children($this->album, 0, true);

			$this->info = AlbumCast::toArrayWith($this->album, $children);
			$this->info['owner'] = $this->album->owner->get_username();

			$thumbs = $this->albumFunctions->get_thumbs($this->album, $children);
			$this->albumFunctions->set_thumbs_children($this->info['albums'], $thumbs[1]);
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
