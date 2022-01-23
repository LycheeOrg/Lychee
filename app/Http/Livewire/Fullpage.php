<?php

namespace App\Http\Livewire;

use App\Contracts\AbstractAlbum;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\Photo;
use App\SmartAlbums\BaseSmartAlbum;
use Livewire\Component;

class Fullpage extends Component
{
	public const ALBUMS = 'albums';
	public const PHOTO = 'photo';
	public const ALBUM = 'album';

	/**
	 * @var
	 */
	public string $mode;
	public ?Photo $photo = null;
	public ?AbstractAlbum $album = null;

	protected $listeners = ['openAlbum', 'openPhoto', 'back'];

	public function mount($albumId = null, $photoId = null)
	{
		$albumFactory = resolve(AlbumFactory::class);
		if ($albumId == null) {
			$this->mode = self::ALBUMS;
		} else {
			$this->mode = self::ALBUM;
			$this->album = $albumFactory->findOrFail($albumId);

			if ($photoId != null) {
				$this->mode = self::PHOTO;
				$this->photo = Photo::with('album')->findOrFail($photoId);
			}
		}
	}

	public function openAlbum($albumId)
	{
		return redirect('/livewire/' . $albumId);
	}

	public function openPhoto($photoId)
	{
		return redirect('/livewire/' . $this->album->id . '/' . $photoId);
	}

	// Ideal we would like to avoid the redirect as they are slow.
	public function back()
	{
		if ($this->photo != null) {
			// $this->photo = null;
			return redirect('/livewire/' . $this->album->id);
		}
		if ($this->album != null) {
			if ($this->album instanceof BaseSmartAlbum) {
				// $this->album = null;
				return redirect('/livewire/');
			}
			if ($this->album instanceof Album && $this->album->parent_id != null) {
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
