<?php

namespace App\Http\Requests\Contracts;

interface HasAlbumID
{
	const ALBUM_ID_ATTRIBUTE = 'albumID';

	/**
	 * @return string|int|null
	 */
	public function albumID();
}
