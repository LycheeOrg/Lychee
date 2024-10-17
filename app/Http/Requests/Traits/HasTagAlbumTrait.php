<?php

namespace App\Http\Requests\Traits;

use App\Models\TagAlbum;

trait HasTagAlbumTrait
{
	protected ?TagAlbum $album = null;

	/**
	 * @return TagAlbum|null
	 */
	public function album(): ?TagAlbum
	{
		return $this->album;
	}
}
