<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Album extends Component
{
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var Album
	 */
	public $album;

	/**
	 * @var array (for now)
	 */
	public $data;

	public function mount($id)
	{
		$this->id = $id;
		$this->album = null;
		$this->data = [];
		$this->data['albums'] = [];
	}

	public function render()
	{
		// Get photos
		// change this for smartalbum
		$this->album = $this->getAlbum($this->id);

		if ($album->smart) {
			$publicAlbums = $this->albumsFunctions->getPublicAlbumsId();
			$album->setAlbumIDs($publicAlbums);
			$return = AlbumCast::toArray($album);
		} else {
			// take care of sub albums
			$children = $this->albumFunctions->get_children($album, 0, true);

			$return = AlbumCast::toArrayWith($album, $children);
			$return['owner'] = $album->owner->get_username();

			$thumbs = $this->albumFunctions->get_thumbs($album, $children);
			$this->albumFunctions->set_thumbs_children($return['albums'], $thumbs[1]);
		}

		// take care of photos
		$full_photo = $return['full_photo'] ?? Configs::get_value('full_photo', '1') === '1';
		$photos_query = $album->get_photos();
		$return['photos'] = $this->albumFunctions->photos($album, $photos_query, $full_photo, $album->get_license());

		$return['id'] = $request['albumID'];
		$return['num'] = strval(count($return['photos']));

		// finalize the loop
		if ($return['num'] === '0') {
			$return['photos'] = false;
		}

		return view('livewire.album');
	}
}
