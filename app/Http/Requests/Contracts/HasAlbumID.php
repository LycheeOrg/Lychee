<?php

namespace App\Http\Requests\Contracts;

interface HasAlbumID
{
	public const ALBUM_ID_ATTRIBUTE = 'albumID';

	/**
	 * @return string|null
	 */
	public function albumID(): ?string;
}
