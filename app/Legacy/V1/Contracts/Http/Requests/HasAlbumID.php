<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

interface HasAlbumID
{
	/**
	 * @return string|null
	 */
	public function albumID(): ?string;
}
