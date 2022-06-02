<?php

namespace App\Http\Requests\Traits;

trait HasTagsTrait
{
	/**
	 * @var string[]|null
	 */
	protected ?array $tags = [];

	/**
	 * @return array|null
	 */
	public function tags(): ?array
	{
		return $this->tags;
	}
}
