<?php

namespace App\Http\Requests\Contracts;

interface HasLicense
{
	public const LICENSE_ATTRIBUTE = 'license';

	/**
	 * @return string|null
	 */
	public function license(): ?string;
}
