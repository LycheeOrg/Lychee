<?php

namespace App\Contracts\Http\Requests;

interface HasLicense
{
	/**
	 * @return string|null
	 */
	public function license(): ?string;
}
