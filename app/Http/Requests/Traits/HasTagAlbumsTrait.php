<?php

namespace App\Http\Requests\Traits;

use Illuminate\Support\Collection;

/**
 * @template T of AbstractAlbum
 */
trait HasTagAlbumsTrait
{
	/**
	 * @var Collection<T>
	 */
	protected Collection $albums;

	/**
	 * @return Collection<T>
	 */
	public function albums(): Collection
	{
		return $this->albums;
	}
}
