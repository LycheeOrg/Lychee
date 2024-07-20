<?php

namespace App\Legacy\V1\Requests\Traits;

trait HasPhotoIDTrait
{
	protected ?string $photoID = null;

	/**
	 * @return ?string
	 */
	public function photoID(): ?string
	{
		return $this->photoID;
	}
}
