<?php

namespace App\Http\Requests\Contracts;

interface HasPassword
{
	/**
	 * @return string|null
	 */
	public function password(): ?string;
}
