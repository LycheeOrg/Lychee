<?php

namespace App\Http\Requests\Traits;

trait HasPhotoIdsTrait
{
	/**
	 * @var string[]
	 */
	protected array $photoIds = [];

	/**
	 * @return string[]
	 */
	public function photoIds(): array
	{
		return $this->photoIds;
	}
}
