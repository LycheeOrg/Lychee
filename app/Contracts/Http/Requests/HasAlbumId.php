<?php

namespace App\Contracts\Http\Requests;

interface HasAlbumId
{
	/**
	 * @return string|null
	 */
	public function albumId(): ?string;
}
