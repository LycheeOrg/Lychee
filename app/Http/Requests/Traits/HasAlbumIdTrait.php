<?php

namespace App\Http\Requests\Traits;

trait HasAlbumIdTrait
{
	protected ?string $albumId = null;

	/**
	 * @return string|null
	 */
	public function albumId(): ?string
	{
		return $this->albumId;
	}
}
