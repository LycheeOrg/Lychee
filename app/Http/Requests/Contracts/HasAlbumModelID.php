<?php

namespace App\Http\Requests\Contracts;

interface HasAlbumModelID extends HasAlbumID
{
	/**
	 * @return int|null
	 */
	public function albumID(): ?int;
}
