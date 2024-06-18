<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

use App\Models\Album;

interface HasParentAlbum
{
	/**
	 * @return Album|null
	 */
	public function parentAlbum(): ?Album;
}
