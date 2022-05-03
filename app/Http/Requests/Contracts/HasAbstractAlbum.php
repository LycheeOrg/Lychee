<?php

namespace App\Http\Requests\Contracts;

use App\Contracts\AbstractAlbum;

interface HasAbstractAlbum
{
	public const ALBUM_ID_ATTRIBUTE = 'albumID';

	/**
	 * @return AbstractAlbum|null
	 */
	public function album(): ?AbstractAlbum;
}
