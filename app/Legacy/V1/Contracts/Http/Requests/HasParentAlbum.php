<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

use App\Models\Album;

interface HasParentAlbum
{
	/**
	 * @return Album|null
	 */
	public function parentAlbum(): ?Album;
}
