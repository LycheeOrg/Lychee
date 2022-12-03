<?php

namespace App\Http\Requests\Contracts;

use App\Models\Album;

interface HasParentAlbum
{
	/**
	 * @return Album|null
	 */
	public function parentAlbum(): ?Album;
}
