<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

use App\Models\Album;

interface HasAlbum extends HasBaseAlbum
{
	/**
	 * @return Album|null
	 */
	public function album(): ?Album;
}
