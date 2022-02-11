<?php

namespace App\Http\Requests\Contracts;

use App\Contracts\AbstractAlbum;
use Illuminate\Database\Eloquent\Collection;

interface HasAlbums
{
	public const ALBUM_IDS_ATTRIBUTE = 'albumIDs';

	/**
	 * @return Collection<AbstractAlbum>
	 */
	public function albums(): Collection;
}
