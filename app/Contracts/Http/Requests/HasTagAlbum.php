<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

use App\Models\TagAlbum;

interface HasTagAlbum extends HasBaseAlbum
{
	/**
	 * @return TagAlbum|null
	 */
	public function album(): ?TagAlbum;
}
