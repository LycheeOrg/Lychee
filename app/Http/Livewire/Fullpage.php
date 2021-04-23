<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Fullpage extends Component
{
	/**
	 * @var
	 */
	public $mode;
	public $albumId = null;
	public $photoId = null;

	protected $listeners = ['openAlbum'];

	public function mount($albumId = null, $photoId = null)
	{
		if ($albumId == null) {
			$this->mode = 'albums';
		} else {
			$this->mode = 'album';
			$this->albumId = $albumId;
			if ($photoId != null) {
				$this->mode = 'photo';
				$this->photoId = $photoId;
			}
		}
	}

	public function openAlbum($albumId)
	{
		return redirect('/livewire/' . $albumId);
	}

	public function render()
	{
		return view('livewire.fullpage');
	}
}
