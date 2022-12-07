<?php

namespace App\Http\Requests\Contracts;

interface HasAlbumIDs
{
	/**
	 * @return string[]
	 */
	public function albumIDs(): array;
}
