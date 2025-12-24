<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http;

use App\Exceptions\Internal\LycheeLogicException;
use Symfony\Component\HttpFoundation\ParameterBag;

class WriteOnceParameterBag extends ParameterBag
{
	/**
	 * Prevent overwriting keys once set.
	 */
	public function set(string $key, mixed $value): void
	{
		if ($this->has($key)) {
			throw new LycheeLogicException("Cannot overwrite read-only request attribute '{$key}'.");
		}

		parent::set($key, $value);
	}
}
