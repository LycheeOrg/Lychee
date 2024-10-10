<?php

namespace App\Http\Requests\Traits;

trait HasDescriptionTrait
{
	protected ?string $description = null;

	/**
	 * @return string|null
	 */
	public function description(): ?string
	{
		return $this->description;
	}
}
