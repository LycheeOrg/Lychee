<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Livewire\Traits;

use App\Models\Album;
use App\Models\Photo;

/**
 * @property Photo $photo
 * @property Photo $subPhoto
 * @property Album $album
 * @property Album $subAlbum
 */
trait CreateTree
{
	final protected function createTree(): void
	{
		$this->photo->album_id = $this->album->id;
		$this->photo->save();
		$this->photo = $this->photo->fresh();

		$this->subPhoto->album_id = $this->subAlbum->id;
		$this->subPhoto->save();
		$this->subPhoto = $this->subPhoto->fresh();

		$this->album->appendNode($this->subAlbum);
		$this->album->save();
		$this->album->fixOwnershipOfChildren();
		$this->album = $this->album->fresh();
		$this->album->load('children', 'photos');
		$this->subAlbum->load('children', 'photos');
	}
}
