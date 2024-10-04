<?php

namespace App\Legacy\V1\Requests\Traits;

trait HasAlbumIDsTrait
{
	/**
	 * @var string[]
	 */
	protected array $albumIDs = [];

	/**
	 * @return string[]
	 */
	public function albumIDs(): array
	{
		return $this->albumIDs;
	}
}
