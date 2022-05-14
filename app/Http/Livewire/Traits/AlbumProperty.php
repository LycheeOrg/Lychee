<?php

namespace App\Http\Livewire\Traits;

use App\Contracts\AbstractAlbum;
use App\Models\Extensions\BaseAlbum;
use App\SmartAlbums\BaseSmartAlbum;
use Exception;

trait AlbumProperty
{
	/**
	 * Album property to support the multiple type.
	 *
	 * @return AbstractAlbum|null
	 */
	public function getAlbumProperty(): ?AbstractAlbum
	{
		return $this->baseAlbum ?? $this->smartAlbum;
	}

	/**
	 * Given an album, load it in the desired attributes.
	 *
	 * @param AbstractAlbum $album to be loaded
	 *
	 * @return void
	 *
	 * @throws Exception album class is not recognized
	 */
	protected function loadAlbum(AbstractAlbum $album): void
	{
		if ($album instanceof BaseSmartAlbum) {
			$this->smartAlbum = $album;
			$this->baseAlbum = null; // ! safety
		} elseif ($album instanceof BaseAlbum) {
			$this->baseAlbum = $album;
			$this->smartAlbum = null; // ! safety
		} else {
			throw new \Exception('unrecognized class for ' . get_class($album));
		}
	}
}
