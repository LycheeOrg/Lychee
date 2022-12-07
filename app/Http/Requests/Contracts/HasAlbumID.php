<?php

namespace App\Http\Requests\Contracts;

interface HasAlbumID
{
	/**
	 * @return string|null
	 */
	public function albumID(): ?string;
}
