<?php

namespace App\Http\Requests\Traits;

trait HasTagsTrait
{
	/**
	 * @var string[]
	 */
	protected array $tags = [];

	/**
	 * @return string[]
	 */
	public function tags(): array
	{
		return $this->tags;
	}
}
