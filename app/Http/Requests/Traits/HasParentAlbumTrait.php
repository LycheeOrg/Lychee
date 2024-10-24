<?php

namespace App\Http\Requests\Traits;

use App\Models\Album;

trait HasParentAlbumTrait
{
	protected ?Album $parent_album = null;

	/**
	 * @return Album|null
	 */
	public function parent_album(): ?Album
	{
		return $this->parent_album;
	}
}
