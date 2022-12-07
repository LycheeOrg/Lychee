<?php

namespace App\Http\Requests\Contracts;

interface HasLicense
{
	/**
	 * @return string|null
	 */
	public function license(): ?string;
}
