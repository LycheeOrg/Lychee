<?php

namespace App\Http\Requests\Traits;

trait HasTagsTrait
{
	protected ?string $tags = null;

	/**
	 * @return string|null
	 */
	public function tags(): ?string
	{
		return $this->tags;
	}
}
