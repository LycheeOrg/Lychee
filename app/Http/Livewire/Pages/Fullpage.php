<?php

namespace App\Http\Livewire\Pages;

use App\Contracts\AbstractAlbum;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\Photo;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Support\Facades\Config;
use Livewire\Component;

class Fullpage extends Component
{
	public const ALBUMS = 'albums';
	public const PHOTO = 'photo';
	public const ALBUM = 'album';

	public string $mode;
	public string $title;
	public ?Photo $photo = null;
	public ?BaseAlbum $baseAlbum = null;
	public ?BaseSmartAlbum $smartAlbum = null;

	protected $listeners = ['openAlbum', 'openPhoto', 'back', 'reloadPage'];

	/**
	 * Album property to support the multiple type.
	 *
	 * @return AbstractAlbum|null
	 */
	public function getAlbumProperty(): ?AbstractAlbum
	{
		return $this->baseAlbum ?? $this->smartAlbum;
	}

	public function mount(?string $albumId = null, ?string $photoId = null)
	{
		$albumFactory = resolve(AlbumFactory::class);
		if ($albumId == null) {
			$this->mode = self::ALBUMS;
			$this->title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
		} else {
			$this->mode = self::ALBUM;
			$album = $albumFactory->findOrFail($albumId);
			if ($album instanceof BaseSmartAlbum) {
				$this->smartAlbum = $album;
				$this->baseAlbum = null; //! safety
			} elseif ($album instanceof BaseAlbum) {
				$this->baseAlbum = $album;
				$this->smartAlbum = null; //! safety
			} else {
				throw new \Exception('unrecognized class for ' . get_class($album));
			}
			$this->title = $album->title;

			if ($photoId != null) {
				$this->mode = self::PHOTO;
				$this->photo = Photo::with('album')->findOrFail($photoId);
				$this->title = $this->photo->title;
			}
		}
	}

	public function render()
	{
		return view('livewire.pages.fullpage');
	}

	/*
	 *          Interactions
	 */

	public function reloadPage()
	{
		if ($this->photo != null) {
			return redirect('/livewire/' . $this->getAlbumProperty()->id . '/' . $this->photo->id);
		}

		return redirect('/livewire/' . $this->getAlbumProperty()?->id ?? '');
	}

	public function openAlbum($albumId)
	{
		return redirect('/livewire/' . $albumId);
	}

	public function openPhoto($photoId)
	{
		return redirect('/livewire/' . $this->getAlbumProperty()->id . '/' . $photoId);
	}

	// Ideal we would like to avoid the redirect as they are slow.
	public function back()
	{
		if ($this->photo != null) {
			// $this->photo = null;
			return redirect('/livewire/' . $this->getAlbumProperty()->id ?? '');
		}
		if ($this->baseAlbum != null) {
			if ($this->baseAlbum instanceof Album && $this->baseAlbum->parent_id != null) {
				return redirect('/livewire/' . $this->baseAlbum->parent_id);
			}

			return redirect('/livewire/');
		}
		if ($this->smartAlbum != null) {
			return redirect('/livewire/');
		}
	}
}
