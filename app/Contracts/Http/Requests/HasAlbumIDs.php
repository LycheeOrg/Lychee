<?php

namespace App\Contracts\Http\Requests;

interface HasAlbumIDs
{
	/**
	 * @return string[]
	 */
	public function albumIDs(): array;
}
