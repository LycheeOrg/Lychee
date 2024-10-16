<?php

namespace App\Legacy\V1\Requests\Traits;

use App\Contracts\Models\AbstractAlbum;

trait HasAbstractAlbumTrait
{
	protected ?AbstractAlbum $album = null;

	/**
	 * @return AbstractAlbum|null
	 */
	public function album(): ?AbstractAlbum
	{
		return $this->album;
	}
}
