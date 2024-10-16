<?php

namespace App\Http\Requests\Traits;

use App\Models\Album;

trait HasAlbumTrait
{
	protected ?Album $album = null;

	/**
	 * @return Album|null
	 */
	public function album(): ?Album
	{
		return $this->album;
	}
}
