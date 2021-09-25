<?php

namespace App\Http\Requests\Contracts;

interface HasAlbumIDs
{
	const ALBUM_IDS_ATTRIBUTE = 'albumIDs';

	/**
	 * @return array
	 */
	public function albumIDs(): array;
}
