<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Http\Requests;

use Illuminate\Support\Carbon;

interface HasUploadDate
{
	/**
	 * @return Carbon|null
	 */
	public function uploadDate(): ?Carbon;
}
