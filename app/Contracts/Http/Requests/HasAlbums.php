<?php

namespace App\Contracts\Http\Requests;

use Illuminate\Support\Collection;

/**
 * @template T of \App\Contracts\Models\AbstractAlbum
 */
interface HasAlbums
{
	/**
	 * @return Collection<int,T>
	 */
	public function albums(): Collection;
}
