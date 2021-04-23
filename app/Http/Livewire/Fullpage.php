<?php

namespace App\Http\Livewire;

use App\Factories\AlbumFactory;
use Livewire\Component;

class Fullpage extends Component
{
	/**
	 * @var
	 */
	public $mode;
	public $photo = null;

	/**
	 * @var Album|SmartAlbum|TagAlbum
	 */
	public $album = null;

	protected $listeners = ['openAlbum', 'back'];

	public function mount($albumId = null, $photoId = null, AlbumFactory $albumFactory)
	{
		if ($albumId == null) {
			$this->mode = 'albums';
		} else {
			$this->mode = 'album';
			$this->album = $albumFactory->make($albumId);
			// $this->albumId = $albumId;
			if ($photoId != null) {
				$this->mode = 'photo';
				$this->photo = $photoId;
			}
		}
	}

	public function openAlbum($albumId)
	{
		return redirect('/livewire/' . $albumId);
	}

	// Ideal we would like to avoid the redirect as they are slow.
	public function back()
	{
		if ($this->photo != null) {
			// $this->photo = null;
			return redirect('/livewire/' . $this->album->id);
		}
		if ($this->album != null) {
			if ($this->album->is_smart()) {
				// $this->album = null;
				return redirect('/livewire/');
			}
			if ($this->album->parent_id != null) {
				return redirect('/livewire/' . $this->album->parent_id);
			}

			return redirect('/livewire/');
		}
	}

	public function render()
	{
		return view('livewire.fullpage');
	}
}
