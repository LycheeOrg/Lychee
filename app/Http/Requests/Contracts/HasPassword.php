<?php

namespace App\Http\Requests\Contracts;

interface HasPassword
{
	const PASSWORD_ATTRIBUTE = 'password';

	/**
	 * @return string|null
	 */
	public function password(): ?string;
}
