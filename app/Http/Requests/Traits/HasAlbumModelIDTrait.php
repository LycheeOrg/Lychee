<?php

namespace App\Http\Requests\Traits;

trait HasAlbumModelIDTrait
{
	/**
	 * @var int|null
	 */
	protected ?int $albumID = null;

	/**
	 * @return int|null
	 */
	public function albumID(): ?int
	{
		return $this->albumID;
	}
}
