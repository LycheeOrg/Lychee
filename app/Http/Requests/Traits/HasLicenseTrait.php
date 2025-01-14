<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

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