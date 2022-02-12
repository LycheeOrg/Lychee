<?php

namespace App\Http\Requests\Traits;

use App\Models\Extensions\BaseAlbum;

trait HasBaseAlbumTrait
{
	protected ?BaseAlbum $album = null;

	/**
	 * @return BaseAlbum|null
	 */
	public function album(): ?BaseAlbum
	{
		return $this->album;
	}
}
