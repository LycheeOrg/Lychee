<?php

namespace App\Contracts\Http\Requests;

interface HasCopyright
{
	/**
	 * @return string|null
	 */
	public function copyright(): ?string;
}
