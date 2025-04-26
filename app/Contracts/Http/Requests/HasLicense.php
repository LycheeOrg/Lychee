<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Http\Requests;

use App\Enum\LicenseType;

interface HasLicense
{
	/**
	 * @return LicenseType|null
	 */
	public function license(): ?LicenseType;
}
