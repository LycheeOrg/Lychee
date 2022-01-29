<?php

namespace App\Http\Livewire;

use App\Contracts\AbstractAlbum;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
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
	public ?BaseAlbum $album = null;
	public ?BaseSmartAlbum $smartAlbum = null;

	protected $listeners = ['openAlbum', 'openPhoto', 'back'];

	public function mount($albumId = null, $photoId = null)
	{
		$albumFactory = resolve(AlbumFactory::class);
		if ($albumId == null) {
			$this->mode = self::ALBUMS;
		} else {
			$this->mode = self::ALBUM;
			$this->album = $albumFactory->findOrFail($albumId);
			$album = $albumFactory->findOrFail($albumId);
			if ($album instanceof BaseSmartAlbum) {
				$this->smartAlbum = $album;
				$this->album = null; //! safety
			} elseif ($album instanceof BaseAlbum) {
				$this->album = $album;
				$this->smartAlbum = null; //! safety
			} else {
				throw new \Exception('unrecognized class for ' . get_class($album));
			}

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
		return redirect('/livewire/' . $this->getAlbumId() . '/' . $photoId);
	}

	// Ideal we would like to avoid the redirect as they are slow.
	public function back()
	{
		if ($this->photo != null) {
			// $this->photo = null;
			return redirect('/livewire/' . $this->getAlbumId());
		}
		if ($this->album != null) {
			if ($this->album instanceof Album && $this->album->parent_id != null) {
				return redirect('/livewire/' . $this->album->parent_id);
			}

			return redirect('/livewire/');
		}
		if ($this->smartAlbum != null) {
			return redirect('/livewire/');
		}
	}

	public function render()
	{
		return view('livewire.fullpage');
	}

	public function getAlbumId(): string
	{
		return $this->album?->id ?? $this->smartAlbum?->id;
	}

	public function getAlbum(): AbstractAlbum
	{
		return $this->album ?? $this->smartAlbum;
	}
}
