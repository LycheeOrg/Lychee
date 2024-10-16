<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

interface HasAlbumIDs
{
	/**
	 * @return string[]
	 */
	public function albumIDs(): array;
}
