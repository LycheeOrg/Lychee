<?php

namespace App\Http\Requests\Traits;

trait HasPasswordTrait
{
	protected ?string $password = null;

	/**
	 * @return string|null
	 */
	public function password(): ?string
	{
		return $this->password;
	}
}
