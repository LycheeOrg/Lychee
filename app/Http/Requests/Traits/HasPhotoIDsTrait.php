<?php

namespace App\Http\Requests\Traits;

trait HasPhotoIDsTrait
{
	protected array $photoIDs = [];

	/**
	 * @return array
	 */
	public function photoIDs(): array
	{
		return $this->photoIDs;
	}
}
