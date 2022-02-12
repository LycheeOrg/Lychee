<?php

namespace App\Http\Requests\Contracts;

use App\Models\TagAlbum;

interface HasTagAlbum extends HasBaseAlbum
{
	/**
	 * @return TagAlbum|null
	 */
	public function album(): ?TagAlbum;
}
