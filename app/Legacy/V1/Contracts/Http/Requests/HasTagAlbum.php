<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

use App\Models\TagAlbum;

interface HasTagAlbum extends HasBaseAlbum
{
	/**
	 * @return TagAlbum|null
	 */
	public function album(): ?TagAlbum;
}
