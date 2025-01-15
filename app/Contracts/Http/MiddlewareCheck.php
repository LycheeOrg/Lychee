<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Http;

use App\Contracts\Exceptions\InternalLycheeException;

interface MiddlewareCheck
{
	/**
	 * @return bool
	 *
	 * @throws InternalLycheeException
	 */
	public function assert(): bool;
}
