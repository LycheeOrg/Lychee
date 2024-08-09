<?php

namespace App\Contracts\Http\Requests;

use App\Enum\LicenseType;

interface HasLicense
{
	/**
	 * @return LicenseType|null
	 */
	public function license(): ?LicenseType;
}
