<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Http\Requests;

interface HasPassword
{
	/**
	 * @return string|null
	 */
	public function password(): ?string;
}
