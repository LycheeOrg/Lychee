<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

use App\Contracts\Exceptions\InternalLycheeException;

class LycheeAssertionError extends \AssertionError implements InternalLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct($msg, $previous !== null ? $previous->getCode() : 0, $previous);
	}

	public static function createFromUnexpectedException(\Throwable $previous): self
	{
		return new self('Unexpected exception: ' . get_class($previous), $previous);
	}
}
