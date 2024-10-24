<?php

namespace App\Http\Requests\Traits;

trait HasTermsTrait
{
	/**
	 * @var string[]
	 */
	protected array $terms;

	/**
	 * @return string[]
	 */
	public function terms(): array
	{
		return $this->terms;
	}
}
