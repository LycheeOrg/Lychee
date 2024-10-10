<?php

namespace App\Contracts\Http\Requests;

interface HasPassword
{
	/**
	 * @return string|null
	 */
	public function password(): ?string;
}
