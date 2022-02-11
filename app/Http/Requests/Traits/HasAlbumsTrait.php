<?php

namespace App\Http\Requests\Traits;

use App\Contracts\AbstractAlbum;
use Illuminate\Database\Eloquent\Collection;

trait HasAlbumsTrait
{
	/**
	 * @var Collection<AbstractAlbum>
	 */
	protected Collection $albums;

	/**
	 * @return Collection<AbstractAlbum>
	 */
	public function albums(): Collection
	{
		return $this->albums;
	}
}
