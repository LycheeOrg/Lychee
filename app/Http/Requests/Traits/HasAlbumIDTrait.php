<?php

declare(strict_types=1);

namespace App\Http\Requests\Traits;

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
