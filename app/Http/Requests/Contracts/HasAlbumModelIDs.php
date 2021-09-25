<?php

namespace App\Http\Requests\Contracts;

interface HasAlbumModelIDs extends HasAlbumIDs
{
	/**
	 * @return int[]
	 */
	public function albumIDs(): array;
}
