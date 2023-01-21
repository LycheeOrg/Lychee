<?php

namespace App\Http\Livewire\Traits;

use App\Contracts\Models\AbstractAlbum;
use App\Models\Extensions\BaseAlbum;
use App\SmartAlbums\BaseSmartAlbum;

/**
 * This trait is use to take care and simulate an AbstractAlbum attribute.
 * AbstractAlbum cannot be a Livewire component attribute because it is an interface.
 * For this reason, we take advantage of the fact that an abstract album is either a baseAlbum or a smartAlbum.
 * Both of the later are wireable.
 * As a consequence, OBJECT->album is effectively being a FACADE for the property.
 *
 * Read more here:
 * https://laravel-livewire.com/docs/2.x/computed-properties
 */
trait AlbumProperty
{
	/**
	 * Album property to support the multiple type.
	 *
	 * @return AbstractAlbum|null
	 */
	final public function getAlbumProperty(): ?AbstractAlbum
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
	 * @throws \Exception album class is not recognized
	 */
	final public function loadAlbum(AbstractAlbum $album): void
	{
		$this->resetAlbums();
		if ($album instanceof BaseSmartAlbum) {
			$this->smartAlbum = $album;
		} elseif ($album instanceof BaseAlbum) {
			$this->baseAlbum = $album;
		} else {
			throw new \Exception('unrecognized class for ' . get_class($album));
		}
	}

	/**
	 * Allows to reset smart and base album in one go.
	 *
	 * @return void
	 */
	final public function resetAlbums(): void
	{
		$this->baseAlbum = null; // ! safety
		$this->smartAlbum = null; // ! safety
	}
}
