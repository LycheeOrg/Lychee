<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

use App\Enum\LicenseType;

interface HasLicense
{
	/**
	 * @return LicenseType|null
	 */
	public function license(): ?LicenseType;
}
