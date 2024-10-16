<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

interface HasPassword
{
	/**
	 * @return string|null
	 */
	public function password(): ?string;
}
