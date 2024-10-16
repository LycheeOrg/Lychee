<?php

namespace App\Legacy\V1\Requests\Traits;

use Illuminate\Support\Collection;

/**
 * @template T of \App\Contracts\Models\AbstractAlbum
 */
trait HasAlbumsTrait
{
	/**
	 * @var Collection<int,T>
	 */
	protected Collection $albums;

	/**
	 * @return Collection<int,T>
	 */
	public function albums(): Collection
	{
		return $this->albums;
	}
}
