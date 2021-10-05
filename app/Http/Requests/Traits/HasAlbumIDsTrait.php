<?php

namespace App\Http\Requests\Traits;

trait HasAlbumIDsTrait
{
	/**
	 * @var array<int|string|null>
	 */
	protected array $albumIDs = [];

	/**
	 * @return array<int|string|null>
	 */
	public function albumIDs(): array
	{
		return $this->albumIDs;
	}
}
