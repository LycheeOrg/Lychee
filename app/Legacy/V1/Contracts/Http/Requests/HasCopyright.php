<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

interface HasCopyright
{
	/**
	 * @return string|null
	 */
	public function copyright(): ?string;
}
