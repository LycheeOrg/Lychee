<?php

namespace App\Http\Requests\Contracts;

use Illuminate\Support\Collection;

/**
 * @template T of \App\Contracts\AbstractAlbum
 */
interface HasAlbums
{
	public const ALBUM_IDS_ATTRIBUTE = 'albumIDs';

	/**
	 * @return Collection<T>
	 */
	public function albums(): Collection;
}
