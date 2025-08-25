<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class ZipInvalidException extends BaseLycheeException
{
	public function __construct(string $message, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_I_AM_A_TEAPOT, $message, $previous);
	}
}
