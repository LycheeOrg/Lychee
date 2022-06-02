<?php

namespace App\Http\Requests\Contracts;

use Illuminate\Support\Collection;

/**
 * @template T of AbstractAlbum
 */
interface HasTagAlbums
{
	public const ALBUM_IDS_ATTRIBUTE = 'albumIDs';

	/**
	 * @return Collection<T>
	 */
	public function albums(): Collection;
}
