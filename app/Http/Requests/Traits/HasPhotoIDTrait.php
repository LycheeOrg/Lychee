<?php

namespace App\Http\Requests\Traits;

trait HasPhotoIDTrait
{
	/**
	 * @var int|null
	 */
	protected ?int $photoID = null;

	/**
	 * @return ?int
	 */
	public function photoID(): ?int
	{
		return $this->photoID;
	}
}
