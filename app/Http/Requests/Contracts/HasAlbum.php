<?php

namespace App\Http\Requests\Contracts;

use App\Models\Extensions\BaseAlbum;

interface HasAlbum
{
	public const ALBUM_ID_ATTRIBUTE = 'albumID';

	/**
	 * @return BaseAlbum|null
	 */
	public function album(): ?BaseAlbum;
}
