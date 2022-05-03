<?php

namespace App\Http\Requests\Contracts;

use App\Models\Album;

interface HasAlbum extends HasBaseAlbum
{
	/**
	 * @return Album|null
	 */
	public function album(): ?Album;
}
