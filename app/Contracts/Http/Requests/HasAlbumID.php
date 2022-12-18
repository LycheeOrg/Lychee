<?php

namespace App\Contracts\Http\Requests;

interface HasAlbumID
{
	/**
	 * @return string|null
	 */
	public function albumID(): ?string;
}
