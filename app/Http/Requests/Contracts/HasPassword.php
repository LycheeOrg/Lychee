<?php

namespace App\Http\Requests\Contracts;

interface HasPassword
{
	public const PASSWORD_ATTRIBUTE = 'password';

	/**
	 * @return string|null
	 */
	public function password(): ?string;
}
