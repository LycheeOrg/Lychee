<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Contracts\Http\Requests;

interface HasCopyright
{
	/**
	 * @return string|null
	 */
	public function copyright(): ?string;
}
