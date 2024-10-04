<?php

namespace App\Legacy\V1\Requests\Traits;

trait HasAlbumIDTrait
{
	protected ?string $albumID = null;

	/**
	 * @return string|null
	 */
	public function albumID(): ?string
	{
		return $this->albumID;
	}
}
