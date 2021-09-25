<?php

namespace App\Http\Requests\Traits;

trait HasAlbumIDsTrait
{
	protected array $albumIDs = [];

	/**
	 * @return array
	 */
	public function albumIDs(): array
	{
		return $this->albumIDs;
	}
}
