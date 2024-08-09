<?php

namespace App\Legacy\V1\Requests\Traits;

use App\Enum\LicenseType;

trait HasLicenseTrait
{
	protected LicenseType $license = LicenseType::NONE;

	/**
	 * @return LicenseType
	 */
	public function license(): LicenseType
	{
		return $this->license;
	}
}