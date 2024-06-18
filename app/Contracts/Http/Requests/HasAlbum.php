<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

use App\Models\Album;

interface HasAlbum extends HasBaseAlbum
{
	/**
	 * @return Album|null
	 */
	public function album(): ?Album;
}
