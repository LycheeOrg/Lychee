<?php

namespace App\Http\Requests\Contracts;

interface HasAlbumIDs
{
	public const ALBUM_IDS_ATTRIBUTE = 'albumIDs';

	/**
	 * @return string[]
	 */
	public function albumIDs(): array;
}
