<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

use App\Enum\LicenseType;

interface HasLicense
{
	/**
	 * @return LicenseType|null
	 */
	public function license(): ?LicenseType;
}
