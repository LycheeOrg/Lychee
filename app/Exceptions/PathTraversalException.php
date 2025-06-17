<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * PathTraversalException.
 *
 * This exception is thrown when a path traversal attack is detected.
 * We throw an error 418 because we use this with fail-to-ban for the honeypot.
 */
class PathTraversalException extends BaseLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_I_AM_A_TEAPOT, $msg, $previous);
	}
}
