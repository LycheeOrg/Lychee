<?php

namespace App\Contracts\Http\Requests;

use App\Models\Album;

interface HasAlbum extends HasBaseAlbum
{
	/**
	 * @return Album|null
	 */
	public function album(): ?Album;
}
