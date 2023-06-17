<?php

namespace App\Http\Requests\Traits;

trait HasCopyrightTrait
{
	protected ?string $copyright = null;

	/**
	 * @return string|null
	 */
	public function copyright(): ?string
	{
		return $this->copyright;
	}
}
