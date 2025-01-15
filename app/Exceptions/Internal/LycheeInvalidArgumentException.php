<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

use App\Contracts\Exceptions\InternalLycheeException;

class LycheeInvalidArgumentException extends \InvalidArgumentException implements InternalLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct($msg, 0, $previous);
	}
}
