<?php

declare(strict_types=1);

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
