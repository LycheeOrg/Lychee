<?php

namespace App\Http\Requests\Traits;

trait HasPhotoIDsTrait
{
	/**
	 * @var string[]
	 */
	protected array $photoIDs = [];

	/**
	 * @return string[]
	 */
	public function photoIDs(): array
	{
		return $this->photoIDs;
	}
}
